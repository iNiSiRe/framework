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
    public function handle(Request $request, Response $response)
    {

    }
}