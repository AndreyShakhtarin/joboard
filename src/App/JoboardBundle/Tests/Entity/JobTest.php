<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 08.02.16
 * Time: 15:22
 */

namespace App\JoboardBundle\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\JoboardBundle\Slug\Joboard;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;

class JobTest
{
    private $em;
    private $application;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->application = new Application(static::$kernel);

        $command = new DropDatabaseDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput(array(
            'command' => 'doctrine:database:drop',
            '--force' => true
        ));
        $command->run($input, new NullOutput());

        $conection = $this->application->getKernel()->getContainer()->get('doctrine')->getConection();
        if($conection->isConected()){
            $conection->close();
        }

        $command = new CreateDatabaseDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput(array(
            'command' => 'doctrine:database:create',
        ));
        $command->run($input, new NullOutput());

        $command = new CreateDatabaseDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:create',
        ));
        $command->run($input, new NullOutput());

        $this->em = static::$karnel->getContainer()
            ->get('doctrine')
            ->getManager();
        $client = static::createClient();
        $loader = new \Symfony\Bridge\Doctrine\ContainerAwareEventManager($client->getContainer());
        $loader->loadFromDirectory(static::$karnel->locateResource('@AppJoboardBundle/DataFixtures/ORM'));
        $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger($this->em);
        $executor = new \Doctrine\Common\DataFixtures\Executor\ORMExecutor($this->em, $purger);
        $executor->execute($loader->getFixture());
    }

    public function testGetCompanySlug()
    {
        $job = $this->em->createQuery('SELECT j FROM AppJoboardBundle:Job j')
        ->setMaxResults(1)
        ->getSingleResult();

        $this->assertEquals($job->getCompanySlug(), Joboard::slugify($job->getCompony()));
    }

    public function testGetPositionSlug()
    {
        $job = $this->em->createQuery('SELECT j FROM AppJoboardBundle:Job j')
            ->setMaxResults(1)
            ->getSingleResult();

        $this->assertEquals($job->getPositionSlug(), Joboard::slugify($job->getPosition()));
    }

    public function testGetLocationSlug()
    {
        $job = $this->em->createQuery('SELECT j FROM AppJoboardBundle:Job j')
            ->setMaxResults(1)
            ->getSingleResult();

        $this->assertEquals($job->getLocationSlug(), Joboard::slugify($job->getLocation()));
    }

    public function testSetExpiresAtValue()
    {
        $job = new Job();
        $job->setExpiresAtValue();
        $this->assertEquals(time() + 86400 * 30, $job->getExpiresAt()->format('U'));
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->em->close();
    }
}