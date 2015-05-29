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
                $request->getVersion()
            );

            $request->on('form.file', function (FormField $field, $originalFilename) use ($kernelRequest) {

                $file = new File();
                $kernelRequest->files->set($field->getName(), $file);

                $filename = tempnam(sys_get_temp_dir(), '');

                $file->setName($filename);
                $file->setOriginalName($originalFilename);

//                $fileDescriptor = $this->filesystem->file($filename);

                $field->on('data', function ($data) use ($filename) {
                    file_put_contents($filename, $data, FILE_APPEND | FILE_BINARY);
//                    $fileDescriptor->open('cw')->then(function ($stream) use ($data) {
//                        $stream->write($data);
//                        $stream->end();
//                    });
                });

                $field->on('end', function ($data) use ($filename) {
                    file_put_contents($filename, $data, FILE_APPEND);
//                    $fileDescriptor->open('cw')->then(function ($stream) use ($data) {
//                        $stream->write($data);
//                        $stream->end();
//                    });
                });
            });

            $request->on('form.field', function (FormField $field) use ($kernelRequest) {
                $field->on('data', function ($data) use ($kernelRequest, $field) {
                    $kernelRequest->atributes->set($field->getName(), $data);
                });
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