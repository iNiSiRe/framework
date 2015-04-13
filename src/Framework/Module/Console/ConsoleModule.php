<?php

namespace Framework\Module\Console;

use Framework\Module;

class ConsoleModule extends Module
{
    public function getConfigurations()
    {
        return [__DIR__ . '/Configuration/configuration.yml'];
    }
}