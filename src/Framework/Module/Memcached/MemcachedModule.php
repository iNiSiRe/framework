<?php

namespace Framework\Module\Memcached;

use Framework\Module;

class MemcachedModule extends Module
{
    public function getConfigurations()
    {
        return [__DIR__ . '/Configuration/configuration.yml'];
    }
}