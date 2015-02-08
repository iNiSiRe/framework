<?php

namespace Framework\Core;

use Slim\Http\Response;

class JsonResponse extends Response
{
    /**
     * @param $jsonBody
     * @param array $headers
     * @param int $statusCode
     */
    public function __construct($jsonBody, $headers = ['Content-Type' => 'application/json'], $statusCode = 200)
    {
        $body = json_encode($jsonBody);
        parent::__construct($body);
    }
}