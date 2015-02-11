<?php

namespace Framework\Core\DependencyInjection;

class Container
{
    private $services = [];
    private $parameters = [];
    private $commands = [];

    /**
     * @param $name
     * @param $class
     */
    public function add($name, $class)
    {
        $this->services[$name] = $class;
    }

    /**
     * @param $name
     * @param $value
     */
    public function addParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    public function addCommand($name, $class)
    {
        $this->commands[$name] = $class;
    }

    /**
     * @param $name
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function get($name) {
        if (!isset($this->services[$name])) {
            throw new \Exception(sprintf('Service "%s" is undefined', $name));
        }

        if (!is_object($this->services[$name])) {
            $serviceInstance = new $this->services[$name]($this);
            $this->services[$name] = $serviceInstance;
        }

        return $this->services[$name];
    }

    /**
     * @param $name
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getParameter($name)
    {
        if (!isset($this->parameters[$name])) {
            throw new \Exception(sprintf('Parameter "%s" is undefined', $name));
        }

        return $this->parameters[$name];
    }

    /**
     * @param $name
     * @throws \Exception
     */
    public function executeCommand($name, $argvInput)
    {
        if (!isset($this->commands[$name])) {
            list ($class, $method) = explode(':', $this->commands[$name]);
            throw new \Exception(sprintf('Command "%s" is undefined', $name));
        }

        $instance = new $this->commands[$name]($this);

        return $instance->execute($argvInput);
    }
}