<?php

namespace Framework\Module\Twig;

use Framework\DependencyInjection\Container\Container;
use Framework\DependencyInjection\Container\ServiceInterface;

abstract class Extension extends \Twig_Extension implements ServiceInterface
{
    /**
     * @var Container
     */
    protected $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    abstract public function getName();
}