<?php

namespace Framework\DependencyInjection\Container;

use Framework\Configuration\Configuration;
use Framework\Foundation\Dictionary;
use Framework\Kernel;

class Container
{
    private $services;

    /**
     * @var Dictionary
     */
    private $parameters;

    /**
     * @var int
     */
    private $environment;

    /**
     * @param int $environment
     */
    public function __construct($environment = Kernel::ENV_DEV)
    {
        $this->environment = $environment;
        $this->services = new Dictionary();
        $this->configuration = new Configuration();
    }

    public function addConfigFile($file)
    {
        $this->configuration->addFile($file);
    }

    public function prepare()
    {
        $this->configuration->load();

        $this->parameters = new Dictionary($this->configuration->get('parameters', []));

        foreach ($this->configuration->get('services', []) as $name => $definition) {
            $this->add($name, $definition['class']);
        }

//        foreach ($this->configuration->get('commands', []) as $name => $command) {
//            $this->addCommand($name, $command['class']);
//        }
    }

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
            $class = $this->services[$name];
            $configuration = $this->configuration->get($name, []);
            $serviceInstance = new $class($this, $configuration);
            $this->services[$name] = $serviceInstance->load();
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
     * @todo Refactor commands (rename this method to getCommand, to CommandInterface add method "run")
     *
     * @param $name
     * @param $argvInput
     *
     * @throws \Exception
     */
    public function executeCommand($name, $argvInput)
    {
        if (!isset($this->commands[$name])) {
            throw new \Exception(sprintf('Command "%s" is undefined', $name));
        }
        list ($class, $method) = explode(':', $this->commands[$name]);

        $instance = new $class($this);

        if (!method_exists($instance, $method)) {
            throw new \Exception(sprintf('Method "%s" doesn\'t exists in "%s"', $method, $class));
        }

        return $instance->$method($argvInput);
    }
}