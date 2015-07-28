<?php

namespace Framework\Http;

class JsonRequestQuery
{
    const CONTENT_TYPE = 'application/json';

    public function __construct($body)
    {
        $this->params = json_decode($body, true);
    }

    /**
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->params[$key]) ? $this->params[$key] : $default;
    }
}