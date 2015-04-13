<?php

namespace Framework\Module\Router;

use Framework\Http\Request;

class Route
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var array
     */
    private $method;

    /**
     * @var string
     */
    private $handler;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @param string $pattern
     * @param string $handler
     * @param array  $method
     */
    public function __construct($name, $pattern, $method = [Request::GET], $handler)
    {
        $this->pattern = $pattern;
        $this->method = $method;
        $this->handler = $handler;
        $this->name = $name;
    }

    /**
     * @param $uri
     * @param $method
     * @param $matches
     *
     * @return bool
     */
    public function match($uri, $method, &$matches)
    {
        if (!in_array($method, $this->method)) {
            return false;
        }

        $regexp = sprintf('#^%s$#', $this->pattern);

        if (preg_match($regexp, $uri, $matches)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }
}