<?php

namespace Framework\Module\Console;

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
        $application->setHelperSet(new HelperSet(['container' => new ContainerHelper($this->container)]));
        foreach ($commands as $name => $class) {
            $command = new $class;
            $application->add($command);
        }

        $this->application = $application;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->application->setAutoExit(false);
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