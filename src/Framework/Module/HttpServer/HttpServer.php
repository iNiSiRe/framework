<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 09.05.15
 * Time: 21:30
 */

namespace Framework\Module\HttpServer;

use Framework\DependencyInjection\Container\Service;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Module\EventDispatcher\EventDispatcher;
use React\EventLoop\Factory;
use React\Socket\Server;

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
        $this->http->on('request', [$this, 'onRequest']);
        $this->socket->listen(8080, '0.0.0.0');
    }

    public function run()
    {
        $this->loop->run();
    }

    public function onRequest(\React\Http\Request $reactRequest, \React\Http\Response $reactResponse)
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->container->get('event_dispatcher');

        $request = Request::createFromReactRequest($reactRequest);

        $reactRequest->on('data', function ($data) use ($request, $dispatcher) {
            $request->setBody($data);
            $dispatcher->dispatch('request', [$request]);
        });

        $request->on('response', function (Response $response) use ($reactResponse) {
            $reactResponse->writeHead($response->getStatusCode(), $response->getHeaders());
            $reactResponse->end($response->getBody());
        });
    }
}