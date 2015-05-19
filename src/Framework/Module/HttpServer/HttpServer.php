<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 09.05.15
 * Time: 21:30
 */

namespace Framework\Module\HttpServer;

use Framework\DependencyInjection\Container\Service;
use Framework\Http\ReactRequestHandler;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Module\EventDispatcher\EventDispatcher;
use Framework\Module\HttpServer\Provider\MultipartRequestProvider;
use React\EventLoop\Factory;
use React\Socket\Server;
use React\Stream\Stream;

class HttpServer extends Service
{
    /**
     * @var Server
     */
    protected $socket;

    /**
     * @var \React\Http\Server
     */
    protected $http;

    /**
     * @var \React\EventLoop\ExtEventLoop|\React\EventLoop\LibEventLoop|\React\EventLoop\LibEvLoop|\React\EventLoop\StreamSelectLoop
     */
    protected $loop;

    /**
     * @throws \React\Socket\ConnectionException
     */
    public function initialize()
    {
        $this->loop = Factory::create();
        $this->socket = new Server($this->loop);
        $this->http = new \React\Http\Server($this->socket);
        $this->http->on('request', [new ReactRequestHandler(), 'handle']);
        $this->socket->listen(8080, '0.0.0.0');

        $request->on('end', function () use ($request, $dispatcher) {
            $dispatcher->dispatch('request', [$request]);
        });
    }

    public function run()
    {
        $this->loop->run();
    }
}