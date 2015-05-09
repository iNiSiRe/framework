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
    public function __construct()
    {
        $this->scope = new EventEmitter();
    }

    public function initialize()
    {
        $listeners = $this->container->configuration->get('listeners', []);

        foreach ($listeners as $event => $listener)
        {
            list($service, $method) = explode(':', $listener);
            $this->listen($event, [$this->container->get($service), $method]);
        }
    }

    public function dispatch($event, $arguments = [])
    {
        $this->scope->emit($event, $arguments);
    }

    public function listen($event, callable $listener)
    {
        $this->scope->on($event, $listener);
    }
}