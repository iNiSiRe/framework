<?php

namespace Framework\Http;

use Framework\Http\Cookie;

class Response
{
    const SET_COOKIE_HEADER = 'Set-Cookie';

    /**
     * @var Cookie[]
     */
    private $cookies = [];

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
     * @param string $body
     * @param array  $headers
     * @param int    $statusCode
     */
    public function __construct($body, $headers = [], $statusCode = 200)
    {
        $this->setBody($body);
        $this->headers = $headers;
        $this->statusCode = $statusCode;
    }


    private function complete()
    {
        $setCookieHeader = '';
        foreach ($this->cookies as $cookie) {
            $setCookieHeader .= (string)$cookie . ';';
        }

        $this->headers[self::SET_COOKIE_HEADER] = $setCookieHeader;
    }

    /**
     * @param Cookie $cookie
     */
    public function addCookie(Cookie $cookie)
    {
        $this->cookies[] = $cookie;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        $this->complete();

        return $this->headers;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        if (null !== $body && !is_string($body) && !is_numeric($body) && !is_callable(array($body, '__toString'))) {
            throw new \UnexpectedValueException(sprintf('The Response content must be a string or object implementing __toString(), "%s" given.', gettype($body)));
        }

        $this->body = (string) $body;
    }
}