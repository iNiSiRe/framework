<?php

namespace Framework\Module\Memcached;

use Framework\DependencyInjection\Container\ServiceBuilder;

class MemcachedBuilder extends ServiceBuilder
{
    public function build()
    {
        $host = $this->configuration->get('host');
        $port = $this->configuration->get('port');

        $memcached = new \Memcached();
        $memcached->addServer($host, $port);

        return $memcached;
    }
}