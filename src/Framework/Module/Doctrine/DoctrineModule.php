<?php

namespace Framework\Module\Doctrine;

use Framework\Module;

class DoctrineModule extends Module
{
    public function getConfigurations()
    {
        return [__DIR__ . '/Configuration/configuration.yml'];
    }
}