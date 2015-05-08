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
        $this->processor = new EventEmitter();
    }

    public function initialize()
    {
        $listeners = $this->container->configuration->get('listeners', []);

        foreach ($listeners as $listener)
        {

        }
    }

    public function dispatch($event, $arguments = [])
    {
        $this->processor->emit($event, $arguments);
    }

    public function listen($event, callable $listener)
    {
        $this->processor->on($event, $listener);
    }
}