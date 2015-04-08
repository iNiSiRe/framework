<?php

namespace Framework;

use Composer\Autoload\ClassLoader;
use Framework\Configuration\ConfigurationLoader;
use Framework\DependencyInjection\Container\Container;
use Framework\DependencyInjection\Container\ContainerHelper;
use Framework\Http\Response;
use Framework\Http\Request;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

class Kernel
{
    const MODE_DEFAULT = 1;
    const MODE_CONSOLE = 2;

    const ENV_DEV = 1;
    const ENV_PROD = 2;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param     $environment
     * @param     $configurationFile
     */
    public function __construct($environment, $configurationFile)
    {
        $loader = new ConfigurationLoader();
        $loader->addFiles([__DIR__ . '/Resources/config.yml', $configurationFile]);
        $this->container = new Container($environment, $loader->load());
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
            $handler = $this->container->get('router')->getHandler($request);
            $arguments = [$request];
            $response = call_user_func_array($handler, $arguments);
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

    public function runCommand()
    {

    }
}