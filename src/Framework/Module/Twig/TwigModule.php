<?php

namespace Framework\Module\Twig;

use Framework\Module;

class TwigModule extends Module
{
    public function getConfigurations()
    {
        return [__DIR__ . '/Configuration/configuration.yml'];
    }
}