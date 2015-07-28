<?php

namespace Framework\Module\Redis;

use Framework\DependencyInjection\Container\ServiceBuilder;

class RedisBuilder extends ServiceBuilder
{
    public function build()
    {
        $host = $this->configuration->get('host');
        $port = $this->configuration->get('port', 6379);

        $redis = new \Redis();
        $redis->connect($host, $port);

        return $redis;
    }
}