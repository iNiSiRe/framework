<?php
/**
 * Created by PhpStorm.
 * User: Pride
 * Date: 3/22/2015
 * Time: 1:36 PM
 */

namespace Framework\Cache;

use Framework\DependencyInjection\Container\ServiceLoader;

class MemcachedLoader extends ServiceLoader
{
    /**
     * @return mixed
     */
    public function load()
    {
        $host = $this->configuration->get('host');
        $port = $this->configuration->get('port');

        $memcached = new \Memcached();
        $memcached->addServer($host, $port);

        return $memcached;
    }
}