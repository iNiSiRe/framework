<?php

namespace Framework\Module\Console;

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Framework\DependencyInjection\Container\ContainerHelper;
use Framework\DependencyInjection\Container\Service;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Console extends Service
{
    /**
     * @var Application
     */
    private $application;

    /**
     *
     */
    public function initialize()
    {
        $commands = $this->container->configuration->get('commands', []);

        $application = new Application();

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine')->getManager();

        $application->setHelperSet(new HelperSet([
            'container' => new ContainerHelper($this->container),
            'em' => new EntityManagerHelper($em),
            'db' => new ConnectionHelper($em->getConnection())
        ]));

        foreach ($commands as $name => $class) {
            $command = new $class;
            $application->add($command);
        }

        $this->application = $application;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param bool $autoExit
     */
    public function run(InputInterface $input = null, OutputInterface $output = null, $autoExit = false)
    {
        $this->application->setAutoExit($autoExit);
        $this->application->run($input, $output);
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }
}