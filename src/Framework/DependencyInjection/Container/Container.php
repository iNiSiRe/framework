<?php

namespace Framework\DependencyInjection\Container;

use Framework\Configuration\ConfigurationLoader;
use Framework\Foundation\Dictionary;
use Framework\Kernel;

class Container
{
    const SECTION_PARAMETERS = 'parameters';
    const SECTION_SERVICES = 'services';

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
     * @param int   $environment
     * @param array $configuration
     */
    public function __construct($environment = Kernel::ENV_DEV, array $configuration)
    {
        $this->environment = $environment;
        $this->parameters = new Dictionary();
        $this->services = new Dictionary();
        $this->compile($configuration);
    }

    public function compile($configuration)
    {
        $parameters = isset($configuration[self::SECTION_PARAMETERS]) ? $configuration[self::SECTION_PARAMETERS] : [];
        $services = isset($configuration[self::SECTION_SERVICES]) ? $configuration[self::SECTION_SERVICES] : [];

        $this->parameters->add($parameters);

        foreach ($services as $name => $definition) {
            $this->services->set($name, $definition);
        }
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
}