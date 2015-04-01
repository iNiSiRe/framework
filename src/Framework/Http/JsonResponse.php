<?php

namespace Framework\Http;

class JsonResponse extends Response
{
    /**
     * @param string $body
     * @param array  $headers
     * @param int    $statusCode
     */
    public function __construct($body, $headers = [], $statusCode = 200)
    {
        $body = json_encode($body);

        parent::__construct($body, array_merge($headers, ['Content-Type' => 'application/json']), $statusCode);
    }
}