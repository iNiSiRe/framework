<?php

namespace Framework\DependencyInjection\Container;

abstract class Service implements ServiceInterface
{
    public static $name = null;

    public static $requiredParameters = [];

    /**
     * @var Container
     */
    protected $container;

    public function configure()
    {

    }

    public function initialize()
    {

    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function __construct(Container $container, array $configuration)
    {
        $this->setContainer($container);
        $this->setConfiguration($configuration);
    }
}