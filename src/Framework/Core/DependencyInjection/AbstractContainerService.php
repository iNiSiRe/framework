<?php

namespace Framework\Core\DependencyInjection;

abstract class AbstractContainerService implements ContainerServiceInterface
{
    /**
     * @var Container
     */
    protected $container;

    public function __constructor(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}