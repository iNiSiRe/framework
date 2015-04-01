<?php

namespace Framework\DependencyInjection;


interface CommandInterface
{
    /**
     * @param $argvInput
     * @return mixed
     */
    public function execute($argvInput);
}