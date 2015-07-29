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
use Framework\Module\HttpServer\HttpServer;

class EventDispatcher extends Service
{
    /**
     * @var \SplQueue
     */
    private $queue;

    public function __construct()
    {
        parent::__construct();
        $this->scope = new EventEmitter();
        $this->queue = new \SplQueue();
    }

    public function initialize()
    {
        /** @var HttpServer $server */
        $server = $this->container->get('http_server');

        // Async dispatcher
        $server->getLoop()->addPeriodicTimer(1, function () {
            $event = $this->queue->dequeue();
            $this->dispatch($event);
        });

        $listeners = $this->container->configuration->get('listeners', []);

        foreach ($listeners as $name => $listener)
        {
            list($service, $method) = explode(':', $listener);
            $this->listen($name, [$this->container->get($service), $method]);
        }
    }

    public function dispatch(EventInterface $event, $asynchronously = false)
    {
        if (!$asynchronously) {
            $this->scope->emit($event->getName(), [$event]);
        } else {
            $this->queue->enqueue($event);
        }
    }

    public function listen($name, callable $listener)
    {
        $this->scope->on($name, $listener);
    }
}