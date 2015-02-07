<?php

namespace Framework\Command;


interface CommandInterface
{
    /**
     * @return mixed
     */
    public function execute();
}