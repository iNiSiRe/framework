<?php

namespace Framework\DependencyInjection\Container;

use Symfony\Component\Console\Helper\Helper;

class ContainerHelper extends Helper
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return 'container';
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}