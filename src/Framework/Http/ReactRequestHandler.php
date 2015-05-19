<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 19.05.15
 * Time: 19:26
 */

namespace Framework\Http;

use Evenement\EventEmitter;
use Framework\Http\Request as KernelRequest;
use Framework\Http\Response as KernelResponse;
use React\Http\Request;
use React\Http\Response;

class ReactRequestHandler extends EventEmitter
{
    const MODE_DEFAULT = 0;
    const MODE_MULTIPART_DATA = 1;

    private $mode = self::MODE_DEFAULT;

    private $dataProcessors = [];

    public function __construct()
    {
        $this->dataProcessors = [
            self::MODE_MULTIPART_DATA => [$this, 'processMultipartData']
        ];
    }

    private function isMultipartRequest(KernelRequest $request)
    {
        return strpos($request->getHeaders()->get('Content-Type'), 'multipart') !== false;
    }

    private function defineMode(KernelRequest $request)
    {
        if ($this->isMultipartRequest($request)) {
            $this->mode = self::MODE_MULTIPART_DATA;
        }
    }

    private function processData(KernelRequest $request, $data)
    {

    }

    public function handle(Request $request, Response $response)
    {
        $kernelRequest = new KernelRequest(
            $request->getMethod(),
            $request->getPath(),
            $request->getQuery(),
            $request->getHeaders(),
            $request->getHttpVersion()
        );

        $this->defineMode($kernelRequest);

        $request->on('data', function ($data) use ($kernelRequest) {
            $this->processData($kernelRequest, $data);
        });

        $request->on('response', function (KernelResponse $kernelResponse) use ($response) {
            $response->writeHead($kernelResponse->getStatusCode(), $kernelResponse->getHeaders());
            $response->end($kernelResponse->getBody());
        });
    }
}