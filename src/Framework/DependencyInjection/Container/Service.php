<?php

namespace Framework\DependencyInjection\Container;

use Framework\DependencyInjection\Exception\MissingRequiredParameter;
use Framework\Foundation\Dictionary;

abstract class Service implements ServiceInterface
{
    public static $import = [];

    protected $requiredParameters = [];

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Dictionary
     */
    protected $configuration;

    /**
     * @param array $configuration
     *
     * @throws MissingRequiredParameter
     */
    public function configure(array $configuration)
    {
        foreach ($this->requiredParameters as $parameter) {
            if (!isset($configuration[$parameter])) {
                throw new MissingRequiredParameter(sprintf('Required parameter "%s" should be passed to service "%s"', $parameter, self::class));
            }
            $this->configuration->set($parameter, $configuration[$parameter]);
        }
    }

    public function initialize()
    {

    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function __construct()
    {
        $this->configuration = new Dictionary();
    }
}