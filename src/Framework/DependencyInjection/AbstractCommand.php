<?php

namespace Framework\DependencyInjection;

abstract class AbstractCommand implements CommandInterface
{
    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $argvInput
     *
     * @return void
     */
    public function execute($argvInput)
    {
        //Command code there
    }
}