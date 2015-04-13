<?php

namespace Framework\Cache;

use Framework\DependencyInjection\Container\ServiceBuilder;

class MemcachedBuilder extends ServiceBuilder
{
    public function build()
    {
        $host = $this->configuration['host'];
        $port = $this->configuration['port'];

        $memcached = new \Memcached();
        $memcached->addServer($host, $port);

        return $memcached;
    }
}