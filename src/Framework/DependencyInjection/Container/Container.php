<?php

namespace Framework\DependencyInjection\Container;

use Framework\Configuration\ConfigurationLoader;
use Framework\Foundation\Dictionary;
use Framework\Kernel;

class Container
{
    const SECTION_PARAMETERS = 'parameters';
    const SECTION_SERVICES = 'services';
    const SECTION_COMMANDS = 'commands';

    /**
     * @var Dictionary
     */
    public $parameters;

    /**
     * @var Dictionary
     */
    public $services;

    /**
     * @var Dictionary
     */
    public $commands;

    /**
     * @var int
     */
    private $environment;

    /**
     * @param int   $environment
     * @param array $configuration
     */
    public function __construct($environment = Kernel::ENV_DEV, array $configuration)
    {
        $this->environment = $environment;
        $this->parameters = new Dictionary();
        $this->services = new Dictionary();
        $this->configuration = new Dictionary($configuration);
        $this->compile();
    }

    public function compile()
    {
        $parameters = $this->configuration->get(self::SECTION_PARAMETERS, []);
        $services = $this->configuration->get(self::SECTION_SERVICES, []);
        $this->parameters->add($parameters);
        $this->services->add($services);
    }

    /**
     * @param $name
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function get($name) {
        if (!$this->services->get($name)) {
            throw new \Exception(sprintf('Service "%s" is undefined', $name));
        }

        if (!$this->services->get($name) instanceof Service) {
            $definition = $this->services->get($name);
            $class = $definition['class'];
            $configuration = isset($definition['configuration']) ? $definition['configuration'] : [];
            $instance = new $class($this, $configuration);

            if ($instance instanceof ServiceBuilder) {
                $instance = $instance->build();
            }

            if (!$instance instanceof Service) {
                throw new \Exception(sprintf('Service "%s" should be instance of "%s"', $name, Service::class));
            }

            $this->services->set($name, $instance);
        }

        return $this->services->get($name);
    }

    /**;
     * @param $name
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getParameter($name)
    {
        if (!$this->parameters->get($name)) {
            throw new \Exception(sprintf('Parameter "%s" is undefined', $name));
        }

        return $this->parameters->get($name);
    }
}