<?php

namespace Framework\Http;

class RedirectResponse extends Response
{
    /**
     * @param $url
     */
    public function __construct($url)
    {
        parent::__construct('', ['Location' => $url], 302);
    }
}