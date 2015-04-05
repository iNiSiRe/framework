<?php

namespace Framework\Router;

use Framework\Configuration\ConfigurationLoader;
use Framework\DependencyInjection\Container\Container;
use Framework\DependencyInjection\Container\Service;
use Framework\Http\Request;

class Router extends Service
{
    public function __construct(Container $container, array $configuration)
    {
        parent::__construct($container, $configuration);

        $loader = new ConfigurationLoader();
        $loader->addFiles(isset($configuration['files']) ? $configuration['files'] : []);
        $routes = $loader->load();

        foreach ($routes as $name => $definition) {
            if (!in_array($definition['method'], ['POST', 'GET'])) {
                throw new \Exception(sprintf('Unavailable action method "%s" in route "%s', $definition['method'], $name));
            }

            $route = new Route($name, $definition['pattern'], $definition['method'], $definition['handler']);
            $this->add($route);
        }

        return true;
    }

    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * @param Route $route
     */
    public function add(Route $route)
    {
        $this->routes[] = $route;
    }

    /**
     * @param Request $request
     *
     * @return Route
     */
    public function getHandler(Request $request)
    {
        foreach ($this->routes as $route) {
            if (!$route->match($request->getUri(), $request->getMethod())) {
                continue;
            }

            return $this->getCallable($route->getHandler());
        }

        return null;
    }

    /**
     * @param $controller
     *
     * @return Callable
     *
     * @throws \Exception
     */
    private function getCallable($controller)
    {
        $parts = explode(':', $controller);
        $class = $parts[0];
        $method = sprintf('%sAction', $parts[1]);
        $object = new $class($this->container);
        if (!method_exists($object, $method)) {
            throw new \Exception(sprintf('Bad controller name %s', $controller));
        }

        return [$object, $method];
    }
}