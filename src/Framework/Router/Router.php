<?php
/**
 * Created by PhpStorm.
 * User: Pride
 * Date: 3/21/2015
 * Time: 1:17 AM
 */

namespace Framework\Router;


use Framework\Http\Request;

class Router
{
    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * @param Route $route
     */
    public function addRoute(Route $route)
    {
        $this->routes[] = $route;
    }

    /**
     * @param Request $request
     *
     * @return Route
     */
    public function getRouteByRequest(Request $request)
    {
        $matched = [];
        foreach ($this->routes as $route) {
            if (!$route->match($request->getUri(), $request->getMethod())) {
                continue;
            }

            $matched[] = $route;
        }

        return array_shift($matched);
    }
}