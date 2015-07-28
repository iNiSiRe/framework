<?php

namespace Framework\Module\Router;

use Framework\DependencyInjection\Container\Service;
use Framework\Http\Request;
use Framework\Exception\ClassNotExistsException;
use Framework\Module\Router\Exception\NotFoundException;

class Router extends Service
{
    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Route
     */
    private $current;

    public function initialize()
    {
        $routes = $this->container->configuration->get('routes', []);

        foreach ($routes as $name => $definition) {
//            if (!in_array($definition['method'], ['POST', 'GET'])) {
//                throw new \Exception(sprintf('Unavailable action method "%s" in route "%s', $definition['method'], $name));
//            }

            $method = isset($definition['methods']) ? $definition['methods'] : ['GET'];

            $route = new Route($name, $definition['pattern'], $method, $definition['handler']);
            $this->add($route);
        }
    }

    /**
     * @param Route $route
     */
    public function add(Route $route)
    {
        $this->routes[$route->getName()] = $route;
    }

    /**
     * @param Request $request
     *
     * @return Route
     *
     * @throws ClassNotExistsException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function handle(Request $request)
    {
        $this->request = $request;

        $controller = null;
        $matches = [];
        foreach ($this->routes as $route) {
            if ($route->match($request->getUrl(), $request->getMethod(), $matches)) {
                $this->current = $route;
                $controller = $this->getCallable($route->getHandler());
                break;
            }
        }

        if ($controller == null) {
            throw new NotFoundException();
        }

        $arguments = array_merge([$request], array_slice($matches, 1));
        $response = call_user_func_array($controller, $arguments);

        if ($response == null) {
            throw new \Exception("Response can't be null");
        }

        return $response;
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

        if (!class_exists($class)) {
            throw new ClassNotExistsException(sprintf('Class not exists "%s"', $class));
        }

        $object = new $class();

        if ($object instanceof Service) {
            $object->setContainer($this->container);
        }

        if (!method_exists($object, $method)) {
            throw new \Exception(sprintf('Bad controller name %s', $controller));
        }

        return [$object, $method];
    }

    public function generateUrl($name, $parameters = [])
    {
        $pattern = $this->routes[$name]->getPattern();
        $format = preg_replace('#\((.+)\)#U', '%s', $pattern);

        $url = call_user_func_array('sprintf', array_merge([$format], $parameters));

        return $url;
    }

    public function isCurrentUrl($url)
    {
        return (bool) ($this->request !== null ? $url == $this->request->getUrl() : false);
    }
}