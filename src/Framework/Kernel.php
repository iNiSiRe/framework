<?php

namespace Framework;

use Composer\Autoload\ClassLoader;
use Framework\Configuration\Configuration;
use Framework\DependencyInjection\Container\Container;
use Framework\Http\Response;
use Framework\Router\Route;
use Framework\Router\Router;
use Framework\Http\Request;
use Symfony\Component\Yaml\Yaml;

class Kernel
{
    const MODE_DEFAULT = 1;
    const MODE_CONSOLE = 2;

    const ENV_DEV = 1;
    const ENV_PROD = 2;

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var Container
     */
    private $container;

    /**
     * @var array
     */
    private $argvInput;

    private $loader;

    /**
     * @var Router
     */
    private $router;

    /**
     * @param $environment
     * @param $configurationFile
     */
    public function __construct($environment, $configurationFile)
    {
        $this->container = new Container($environment);
        $this->container->addConfigFile(__DIR__ . '/Resources/config.yml');
        $this->container->addConfigFile($configurationFile);
        $this->container->prepare();
    }

    public function run($mode = self::MODE_DEFAULT)
    {
        switch ($mode) {
            case self::MODE_DEFAULT:
                $this->loadRouter();
                break;

            case self::MODE_CONSOLE:
                $this->runCommand();
                break;
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleRequest(Request $request)
    {
        try {
            $handler = $this->router->getRouteByRequest($request)->getHandler();
            $controller = $this->getCallable($handler);
            $arguments = [$request];
            $response = call_user_func_array($controller, $arguments);
        } catch (\Exception $e) {

            $errorBody = sprintf('Uncaught exception "%s" with message "%s" in file "%s" on line %s',
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );

            $response = new Response($errorBody, ['Content-Type' => 'text/html'], 500);
        }

        return $response;
    }

    public function setArgvInput($argv)
    {
        $this->argvInput = $argv;
    }

    public function runCommand()
    {
        if (!isset($this->argvInput[1])) {
            return false;
        }

        $commandName = $this->argvInput[1];
        $this->container->executeCommand($commandName, $this->argvInput);
    }

    /**
     * Load routes
     *
     * @throws \Exception
     */
    private function loadRouter()
    {
        $this->router = new Router();

        foreach ($this->config['routes'] as $name => $params) {

            if (!in_array($params['method'], ['POST', 'GET'])) {
                throw new \Exception(sprintf('Unavailable action method "%s" in route "%s', $params['method'], $name));
            }

            $route = new Route($name, $params['pattern'], $params['method'], $params['handler']);
            $this->router->addRoute($route);
        }

        return true;
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
        $object = new $class($this->container, $this->config);
        if (!method_exists($object, $method)) {
            throw new \Exception(sprintf('Bad controller name %s', $controller));
        }

        return [$object, $method];
    }

    /**
     * @return ClassLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }
}