<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 09.05.15
 * Time: 21:30
 */

namespace Framework\Module\HttpServer;

use Framework\DependencyInjection\Container\Service;
use Framework\Http\File;
use Framework\Http\Request as KernelRequest;
use Framework\Http\Response as KernelResponse;
use Framework\Module\EventDispatcher\EventDispatcher;
use React\EventLoop\Factory;
use React\Filesystem\Filesystem;
use React\Http\Handler\RequestHandler;
use React\Http\Processor\FormField;
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

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->container->get('event_dispatcher');

        $this->http->on('request', function (\React\Http\Request $request, \React\Http\Response $response) use ($dispatcher) {

            $kernelRequest = new KernelRequest(
                $request->getMethod(),
                $request->getPath(),
                $request->getQuery(),
                $request->headers,
                $request->getVersion()
            );

            $kernelRequest->on('response', function (KernelResponse $kernelResponse) use ($response) {
                $response->writeHead($kernelResponse->getStatusCode(), $kernelResponse->getHeaders());
                $response->end($kernelResponse->getBody());
            });

            $kernelRequest->on('end', function () use ($dispatcher, $kernelRequest) {
                $dispatcher->dispatch('request', [$kernelRequest]);
            });

            $requestHandler = new RequestHandler();

            $requestHandler->on('end', function () use ($kernelRequest) {
                $kernelRequest->emit('end');
            });

            $requestHandler->on('data', function (FormField $field) use ($kernelRequest) {
                if ($field->isFile()) {

                    $filename = tempnam(sys_get_temp_dir(), '');

                    $kernelRequest->files->set($field->getName(), null);

                    $dataHandler = function ($data) use ($filename, $kernelRequest, $field) {

                        if (!$data) {
                            return;
                        }

                        if ($kernelRequest->files->get($field->getName()) === null) {
                            $file = new File();
                            $file->setName($filename);
                            $file->setOriginalName($field->attributes->get(FormField::ORIGINAL_FILENAME));
                            $kernelRequest->files->set($field->getName(), $file);
                        }

                        file_put_contents($filename, $data, FILE_APPEND | FILE_BINARY);
                    };
                } else {
                    $dataHandler = function ($data) use ($kernelRequest, $field) {
                        $oldData = $kernelRequest->atributes->get($field->getName());
                        if ($oldData) {
                            $data = $oldData . $data;
                        }
                        $kernelRequest->atributes->set($field->getName(), $data);
                    };
                }

                $field->on('data', $dataHandler);
            });

            $requestHandler->handle($request);
        });

        $this->socket->listen(8080, '0.0.0.0');
    }

    public function run()
    {
        $this->loop->run();
    }
}