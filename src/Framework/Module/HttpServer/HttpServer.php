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
use Framework\Module\HttpServer\Event\RequestEvent;
use React\EventLoop\Factory;
use React\Filesystem\Filesystem;
use React\Http\Handler\RequestHandler;
use React\Http\Processor\FormField;
use React\Socket\Server;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

        $this->http->on('request', function (\React\Http\Request $request, \React\Http\Response $response) {

            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->container->get('event_dispatcher');

            $kernelRequest = new KernelRequest(
                $request->getMethod(),
                $request->getPath(),
                $request->getQuery(),
                $request->headers,
                $request->getVersion()
            );

            $kernelRequest->findClientIp();

            $kernelRequest->on('response', function (KernelResponse $kernelResponse) use ($response) {
                $response->writeHead($kernelResponse->getStatusCode(), $kernelResponse->getHeaders());
                $response->end($kernelResponse->getBody());
            });

            $kernelRequest->on('end', function () use ($dispatcher, $kernelRequest) {
                $dispatcher->dispatch(new RequestEvent($kernelRequest));
            });

            $requestHandler = new RequestHandler();

            $requestHandler->on('end', function () use ($kernelRequest) {
                $kernelRequest->emit('end');
            });

            $requestHandler->on('data', function (FormField $field) use ($kernelRequest) {
                if ($field->isFile()) {
                    $filename = tempnam(sys_get_temp_dir(), '');
                    $dataHandler = function ($data) use ($filename) {
                        file_put_contents($filename, $data, FILE_APPEND | FILE_BINARY);
                    };
                    $endDataHandler = function ($data) use ($filename, $field, $kernelRequest, $dataHandler) {
                        $dataHandler($data);
                        if (!filesize($filename)) {
                            return;
                        }
                        $file = new UploadedFile(
                            $filename,
                            $field->attributes->get(FormField::ORIGINAL_FILENAME),
                            null, null, null, true
                        );
                        parse_str($field->getName(), $data);
                        $key = key($data);
                        $parent = $data[$key];
                        $parent[key($parent)] = $file;
                        $kernelRequest->files->set($key, $parent);
                    };
                } else {
                    $total = '';
                    $dataHandler = function ($data) use (&$total) {
                        if (is_array($data)) {
                            $total = $data;
                        } else {
                            $total .= $data;
                        }
                    };
                    $endDataHandler = function ($data) use (&$total, $dataHandler, $kernelRequest, $field) {
                        $dataHandler($data);
                        $value = $kernelRequest->atributes->get($field->getName());
                        if ($value === null) {
                            $kernelRequest->atributes->set($field->getName(), $total);
                        } elseif (is_array($value)) {
                            $value[] = $total;
                            $kernelRequest->atributes->set($field->getName(), $value);
                        } else {
                            $kernelRequest->atributes->set($field->getName(), [$value, $total]);
                        }
                    };
                }
                $field->on('data', $dataHandler);
                $field->on('end', $endDataHandler);
            });

            $requestHandler->handle($request);
        });

        $this->socket->listen(8080, '0.0.0.0');
    }

    public function run()
    {
        $this->loop->run();
    }

    /**
     * @return \React\EventLoop\ExtEventLoop|\React\EventLoop\LibEventLoop|\React\EventLoop\LibEvLoop|\React\EventLoop\StreamSelectLoop
     */
    public function getLoop()
    {
        return $this->loop;
    }
}