<?php

namespace Framework\Module\Administration;

use Framework\Module;

class AdministrationModule extends Module
{
    public function getConfigurations()
    {
        return [__DIR__ . '/Configuration/config.yml'];
    }
}