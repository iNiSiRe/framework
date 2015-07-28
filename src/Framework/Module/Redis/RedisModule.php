<?php

namespace Framework\Module\Redis;

use Framework\Module;

class RedisModule extends Module
{
    public function getConfigurations()
    {
        return [__DIR__ . '/Configuration/configuration.yml'];
    }
}