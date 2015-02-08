<?php

namespace Framework\Core\DependencyInjection;


interface CommandInterface
{
    /**
     * @param $argvInput
     * @return mixed
     */
    public function execute($argvInput);
}