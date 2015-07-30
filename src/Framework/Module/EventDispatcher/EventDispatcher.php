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
use React\EventLoop\Timer\Timer;

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
        $this->queue->setIteratorMode(\SplQueue::IT_MODE_DELETE);
    }

    public function initialize()
    {
        $listeners = $this->container->configuration->get('listeners', []);

        foreach ($listeners as $name => $listener) {
            list($service, $method) = explode(':', $listener);
            $this->listen($name, [$this->container->get($service), $method]);
        }

        /** @var HttpServer $server */
        $server = $this->container->get('http_server');

        // Run async dispatcher
        $server->getLoop()->addPeriodicTimer(Timer::MIN_INTERVAL * 10, function () {
            if ($this->queue->isEmpty()) {
                return;
            }
            $event = $this->queue->dequeue();
            $this->dispatch($event);
        });
    }

    /**
     * @param EventInterface $event
     * @param bool           $asynchronously
     */
    public function dispatch(EventInterface $event, $asynchronously = false)
    {
        if (!$asynchronously) {
            $this->scope->emit($event->getName(), [$event]);
        } else {
            $this->queue->enqueue($event);
        }
    }

    /**
     * @param string   $name
     * @param callable $listener
     */
    public function listen($name, callable $listener)
    {
        $this->scope->on($name, $listener);
    }
}