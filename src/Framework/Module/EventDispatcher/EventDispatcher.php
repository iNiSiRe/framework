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

    private function run()
    {
        /** @var HttpServer $server */
        $server = $this->container->get('http_server');

        // Run async dispatcher
        $server->getLoop()->addPeriodicTimer(Timer::MIN_INTERVAL, function (Timer $timer) {
            if ($this->queue->isEmpty()) {
                $timer->cancel();
                return;
            }
            $event = $this->queue->dequeue();
            $this->dispatch($event);
        });
    }

    public function initialize()
    {
        $listeners = $this->container->configuration->get('listeners', []);

        foreach ($listeners as $name => $listener) {
            list($service, $method) = explode(':', $listener['handler']);
            $this->listen($listener['event'], [$this->container->get($service), $method]);
        }
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
            $this->run();
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