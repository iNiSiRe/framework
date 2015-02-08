<?php

namespace Framework\Core;

class Response
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @param $body
     * @param array $headers
     * @param int $statusCode
     */
    public function __construct($body, $headers = [], $statusCode = 200)
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->statusCode = $statusCode;
    }
}