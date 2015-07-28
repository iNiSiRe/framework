<?php

namespace Framework\Module\Router;

use Framework\Module;

class RouterModule extends Module
{
    public function getConfigurations()
    {
        return [__DIR__ . '/Configuration/configuration.yml'];
    }
}