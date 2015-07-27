<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 08.05.15
 * Time: 15:51
 */

namespace Framework\Module\EventDispatcher;

use Evenement\EventEmitter;
use Framework\DependencyInjection\Container\Service;

class EventDispatcher extends Service
{
    /**
     * @var \Redis
     */
    private $redis;

    public function __construct()
    {
        parent::__construct();
        $this->scope = new EventEmitter();
    }

    public function initialize()
    {
        $listeners = $this->container->configuration->get('listeners', []);

        $this->redis = $this->container->get('redis');

        foreach ($listeners as $event => $listener)
        {
            list($service, $method) = explode(':', $listener);
            $this->listen($event, [$this->container->get($service), $method]);
        }
    }

    public function dispatch($name, $event, $asynchronously = false)
    {
        if (!$asynchronously) {
            $this->scope->emit($name, [$event]);
        } else {

        }
    }

    public function listen($event, callable $listener)
    {
        $this->scope->on($event, $listener);
    }

    private function dispatchAsynchronously($name, $event)
    {
        $key = 'dispatcher:' . $name;
        $data = serialize($event);
        $this->redis->lPush($key, $data);
    }
}