<?php

namespace Framework\DependencyInjection\Container;

abstract class Service implements ServiceInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $configuration;

    protected function configure()
    {

    }

    protected function initialize()
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