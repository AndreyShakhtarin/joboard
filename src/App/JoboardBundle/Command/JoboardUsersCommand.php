<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 20.10.16
 * Time: 21:34
 */

namespace App\JoboardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\JoboardBundle\Entity\User;

class JoboardUsersCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('app:joboard:users')
            ->setDescription('Добавление пользователей')
            ->addArgument('username', InputArgument::REQUIRED, 'Логин')
            ->addArgument('password', InputArgument::REQUIRED, 'Пароль')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $em = $this->getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setUsername($username);
        $factory = $this->getContainer()->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $encodedPassword = $encoder->encodePassword($password, $user->getSalt());
        $user->setPassword($encodedPassword);
        $em->persist($user);
        $em->flush();

        $output->writeln(sprintf('Добавлен пользователь %s с паролем %s', $username, $password));
    }
}