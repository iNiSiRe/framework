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
use Framework\Http\Request as KernelRequest;
use Framework\Http\Response;
use Framework\Http\Response as KernelResponse;
use Framework\Module\EventDispatcher\EventDispatcher;
use Framework\Module\HttpServer\Provider\MultipartRequestProvider;
use React\EventLoop\Factory;
use React\Filesystem\Filesystem;
use React\Http\Foundation\File;
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
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @throws \React\Socket\ConnectionException
     */
    public function initialize()
    {
        $this->loop = Factory::create();
        $this->socket = new Server($this->loop);
        $this->http = new \React\Http\Server($this->socket);

        $this->filesystem = Filesystem::create($this->loop);

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->container->get('event_dispatcher');

        $this->http->on('request', function (\React\Http\Request $request, \React\Http\Response $response) use ($dispatcher) {

            $kernelRequest = new KernelRequest(
                $request->getMethod(),
                $request->getPath(),
                $request->getQuery(),
                $request->getHeaders(),
                $request->getHttpVersion()
            );

            $request->on('form.file', function ($fieldName, File $file) use ($kernelRequest) {
                $kernelRequest->files->set($fieldName, $file);

                $filename = tempnam(sys_get_temp_dir(), '');

                $file->setPath($filename);

//                $fileDescriptor = $this->filesystem->file($filename);

                $file->on('data', function ($data) use ($filename) {
                    file_put_contents($filename, $data, FILE_APPEND);
//                    $fileDescriptor->open('cw')->then(function ($stream) use ($data) {
//                        $stream->write($data);
//                        $stream->end();
//                    });
                });

                $file->on('end', function ($data) use ($filename) {
                    file_put_contents($filename, $data, FILE_APPEND);
//                    $fileDescriptor->open('cw')->then(function ($stream) use ($data) {
//                        $stream->write($data);
//                        $stream->end();
//                    });
                });
            });

            $request->on('form.field', function ($name, $value) use ($kernelRequest) {
                $kernelRequest->atributes->set($name, $value);
            });

            $request->on('end', function () use ($kernelRequest) {
                $kernelRequest->emit('ready');
            });

            $kernelRequest->on('response', function (KernelResponse $kernelResponse) use ($response) {
                $response->writeHead($kernelResponse->getStatusCode(), $kernelResponse->getHeaders());
                $response->end($kernelResponse->getBody());
            });

            $kernelRequest->on('ready', function () use ($dispatcher, $kernelRequest) {
                $dispatcher->dispatch('request', [$kernelRequest]);
            });
        });

        $this->socket->listen(8080, '0.0.0.0');
    }

    public function run()
    {
        $this->loop->run();
    }
}