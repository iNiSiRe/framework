<?php

namespace Framework;

use Slim\Slim;
use Symfony\Component\Yaml\Yaml;

class Kernel
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * @var Slim
     */
    private $application;

    public function __construct()
    {
        $this->application = new Slim();

        $this->loadConfig();
        $this->loadRouter();

        $this->application->run();
    }

    private function loadConfig()
    {
        $configFile = ROOT_DIR . '/config/config.yml';

        if (!file_exists($configFile)) {
            throw new \Exception(sprintf('Config file %s not exists', $configFile));
        }

        $configContent = file_get_contents($configFile);

        $this->config = Yaml::parse($configContent);
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

        if (!method_exists($object, $method)) {
            throw new \Exception(sprintf('Bad controller name %s', $controller));
        }

        return [$object, $method];
    }
}