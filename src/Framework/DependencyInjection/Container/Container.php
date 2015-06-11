<?php

namespace Framework\DependencyInjection\Container;

use Framework\Configuration\ConfigurationLoader;
use Framework\DependencyInjection\Exception\NotInstanceOfServiceException;
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
    public $configuration;

    /**
     * @var int
     */
    public $environment;

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

    private function createServiceInstance($class, $configuration)
    {
        $instance = new $class;

        if (!$instance instanceof Service) {
            throw new NotInstanceOfServiceException;
        }

        $instance->setContainer($this);
        $instance->configure($configuration);
        $instance->initialize();

        if ($instance instanceof ServiceBuilder) {
            $instance = $instance->build();
        }

        return $instance;
    }

    /**
     * Replace in configurations parameters variables
     * Example: %database_host% -> 127.0.0.1
     *
     * @param $configuration
     */
    private function replaceConfigurationParameters(&$configuration)
    {
        foreach ($configuration as $key => &$value) {
            if (strpos($value, '%') === false) {
                continue;
            }
            $value = preg_replace_callback('/%(.+)%/U', function ($matches) {
                return $this->parameters->get($matches[1]);
            }, $value);
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

        if (!is_object($this->services->get($name))) {
            $class = $this->services->get($name);

            try {
                $configuration = $this->configuration->get($name, []);
                $this->replaceConfigurationParameters($configuration);
                $instance = $this->createServiceInstance($class, $configuration);
            } catch (NotInstanceOfServiceException $e) {
                throw new \Exception(sprintf('Service "%s" should be instance of "%s", instance of "%s" given', $name, Service::class, $class));
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