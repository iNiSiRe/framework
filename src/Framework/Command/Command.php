<?php

namespace Framework\Command;

use Framework\DependencyInjection\Container\Container;
use Framework\DependencyInjection\Container\ContainerHelper;
use Framework\DependencyInjection\Container\Service;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

class Command extends Service
{
    protected $configSection = 'commands';

    protected $requiredParameters = [];

    private $application;

    public function __construct(Container $container, array $configuration)
    {
        parent::__construct($container, $configuration);

        $this->initialize();
    }

    public function initialize()
    {
        $application = new Application();
        $application->setHelperSet(new HelperSet(['container' => new ContainerHelper($this->container)]));
        foreach ($this->container->commands->all() as $name => $class) {
            $command = new $class;
            $application->add($command);
        }

        $this->application = $application;
    }

    public function run()
    {
        $this->application->run();
    }
}