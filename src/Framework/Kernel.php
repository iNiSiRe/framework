<?php

namespace Framework;

use Framework\Core\DependencyInjection\Container;
use Slim\Slim;
use Symfony\Component\Yaml\Yaml;

class Kernel
{
    const MODE_DEFAULT = 1;
    const MODE_CONSOLE = 2;

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var Slim
     */
    private $application;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var array
     */
    private $argvInput;

    public function __construct()
    {
        $this->application = new Slim();

        $this->config = array_merge(
            $this->loadConfig(__DIR__ . '/Resources/config.yml'),
            $this->loadConfig(ROOT_DIR . '/config/config.yml')
        );

        $this->compileContainer();
    }

    public function run($mode = self::MODE_DEFAULT)
    {
        switch ($mode) {
            case self::MODE_DEFAULT:
                $this->loadRouter();
                $this->application->run();
                break;

            case self::MODE_CONSOLE:
                $this->runCommand();
                break;
        }
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
     * Compile all services in container
     */
    private function compileContainer()
    {
        $container = new Container();

        foreach ($this->config['services'] as $name => $service) {
            $container->add($name, $service['class']);
        }

        foreach ($this->config['parameters'] as $name => $value) {
            $container->addParameter($name, $value);
        }

        foreach ($this->config['commands'] as $name => $command) {
            $container->addCommand($name, $command['class']);
        }

        $this->container = $container;
    }

    /**
     * @param string $file
     *
     * @return array
     *
     * @throws \Exception
     */
    private function loadConfig($file)
    {
        if (!file_exists($file)) {
            throw new \Exception(sprintf('Config file %s not exists', $file));
        }

        $configContent = file_get_contents($file);

        return Yaml::parse($configContent);
    }

    /**
     * Load routes
     *
     * @throws \Exception
     */
    private function loadRouter()
    {
        $application = $this->application;

        $availableMethods = ['GET', 'POST', 'PUT', 'DELETE'];

        foreach ($this->config['routes'] as $name => $params) {

            if (!in_array($params['method'], $availableMethods)) {
                throw new \Exception(sprintf('Unavailable action method "%s" in route "%s', $params['method'], $name));
            }

            $requestMethod = mb_strtolower($params['method']);

            $this->application->$requestMethod($params['pattern'],
                function () use ($application, $params) {
                    $args = array_merge( [$application->request], func_get_args());
                    $controller = $this->getCallable($params['controller']);
                    $response = call_user_func_array($controller, $args);
                    $application->response->setBody($response->getBody());
                })
                ->name($name);
        }
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
        $object = new $class();
        $object->setContainer($this->container);
        if (!method_exists($object, $method)) {
            throw new \Exception(sprintf('Bad controller name %s', $controller));
        }

        return [$object, $method];
    }
}