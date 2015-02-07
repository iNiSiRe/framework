<?php

namespace Framework\Command;

use Framework\Container\Container;

abstract class Command implements CommandInterface
{
    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function execute() {

    }
}