<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 30.08.16
 * Time: 15:08
 */

namespace App\JoboardBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;

class CategoryControllerTest extends WebTestCase
{
    private $em;
    private $application;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->application = new Application(static::$kernel);

        // удаляем базу
        $command = new DropDatabaseDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput(array(
            'command' => 'doctrine:database:drop',
            '--force' => true
        ));
        $command->run($input, new NullOutput());

        // закрываем соединение с базой
        $connection = $this->application->getKernel()->getContainer()->get('doctrine')->getConnection();
        if ($connection->isConnected()) {
            $connection->close();
        }

        // создаём базу
        $command = new CreateDatabaseDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput(array(
            'command' => 'doctrine:database:create',
        ));
        $command->run($input, new NullOutput());

        // создаём структуру
        $command = new CreateSchemaDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:create',
        ));
        $command->run($input, new NullOutput());

        // получаем Entity Manager
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // загружаем фикстуры
        $client = static::createClient();
        $loader = new \Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader($client->getContainer());
        $loader->loadFromDirectory(static::$kernel->locateResource('@AppJoboardBundle/DataFixtures/ORM'));
        $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger($this->em);
        $executor = new \Doctrine\Common\DataFixtures\Executor\ORMExecutor($this->em, $purger);
        $executor->execute($loader->getFixtures());
    }

    public function testShow()
    {
        $client = static::createClient();
        $kernel = static::createKernel();
        $kernel->boot();

        // получаем параметры из конфига app/config.yml
        $maxJobsOnCategory = $kernel->getContainer()->getParameter('max_jobs_on_category');
        $maxJobsOnHomepage = $kernel->getContainer()->getParameter('max_jobs_on_homepage');

        $categories = $this->em->getRepository('AppJoboardBundle:Category')->getWithJobs();

        // Категории на домашней странице должны быть кликабельны
        foreach($categories as $category) {
            $crawler = $client->request('GET', '/job/');

            $link = $crawler->selectLink($category->getName())->link();
            $crawler = $client->click($link);

            $this->assertEquals('App\JoboardBundle\Controller\CategoryController::showAction', $client->getRequest()->attributes->get('_controller'));
            $this->assertEquals($category->getSlug(), $client->getRequest()->attributes->get('slug'));

            $jobsNo = $this->em->getRepository('AppJoboardBundle:Job')->countActiveJobs($category->getId());

            // категории в которых вакансий больше чем $maxJobsOnHomepage должны иметь ссылку "Ещё"
            if ($jobsNo > $maxJobsOnHomepage) {
                $crawler = $client->request('GET', '/job/');
                $link = $crawler->filter(".category-" . $category->getId() . " .more-jobs a")->link();
                $crawler = $client->click($link);

                $this->assertEquals('App\JoboardBundle\Controller\CategoryController::showAction', $client->getRequest()->attributes->get('_controller'));
                $this->assertEquals($category->getSlug(), $client->getRequest()->attributes->get('slug'));
            }

            $pages = ceil($jobsNo/$maxJobsOnCategory);

            // только $maxJobsOnCategory вакансий
            $this->assertTrue($crawler->filter('.jobs tr')->count() <= $maxJobsOnCategory);
            $this->assertRegExp("#" . $jobsNo . " вакансии#iu", $crawler->filter('.pagination_desc')->text());

            if ($pages > 1) {
                $this->assertRegExp("#страница 1/" . $pages . "#iu", $crawler->filter('.pagination_desc')->text());

                for ($i = 2; $i <= $pages; $i++) {
                    $link = $crawler->selectLink($i)->link();
                    $crawler = $client->click($link);

                    $this->assertEquals('App\JoboardBundle\Controller\CategoryController::showAction', $client->getRequest()->attributes->get('_controller'));
                    $this->assertEquals($i, $client->getRequest()->attributes->get('page'));
                    $this->assertTrue($crawler->filter('.jobs tr')->count() <= $maxJobsOnCategory);
                    if($jobsNo >1) {
                        $this->assertRegExp("#" . $jobsNo . " вакансии#iu", $crawler->filter('.pagination_desc')->text());
                    }
                    $this->assertRegExp("#страница " . $i . "/" . $pages . "#iu", $crawler->filter('.pagination_desc')->text());
                }
            }
        }
    }
}