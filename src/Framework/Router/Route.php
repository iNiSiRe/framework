<?php
/**
 * Created by PhpStorm.
 * User: Pride
 * Date: 3/21/2015
 * Time: 1:18 AM
 */

namespace Framework\Router;

use Framework\Http\Request;

class Route
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
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
     * @param string $method
     */
    public function __construct($name, $pattern, $method = Request::GET, $handler)
    {
        $this->pattern = $pattern;
        $this->method = $method;
        $this->handler = $handler;
        $this->name = $name;
    }

    /**
     * @param $uri
     * @param $method
     *
     * @return bool
     */
    public function match($uri, $method)
    {
        if ($method != $this->method) {
            return false;
        }

        $regexp = sprintf('#%s#', $this->pattern);

        if (preg_match($regexp, $uri)) {
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
}