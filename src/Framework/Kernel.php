<?php

namespace Framework;

use Application\ApplicationModule;
use Composer\Autoload\ClassLoader;
use Doctrine\ORM\EntityManager;
use Framework\Configuration\ConfigurationLoader;
use Framework\DependencyInjection\Container\Container;
use Framework\Http\Response;
use Framework\Http\Request;
use Framework\Module\Administration\AdministrationModule;
use Framework\Module\Console\ConsoleModule;
use Framework\Module\Doctrine\DoctrineModule;
use Framework\Module\Memcached\MemcachedModule;
use Framework\Module\Router\RouterModule;
use Framework\Module\Twig\TwigModule;

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
     * @var Module[]
     */
    private $modules;

    /**
     * @param     $environment
     * @param     $configurationFile
     */
    public function __construct($environment, $configurationFile)
    {
        $loader = new ConfigurationLoader();

        $this->modules = [
            new MemcachedModule(),
            new RouterModule(),
            new DoctrineModule(),
            new TwigModule(),
            new ConsoleModule(),
            new AdministrationModule(),
            new ApplicationModule()
        ];

        foreach ($this->modules as $module)
        {
            $loader->addFiles($module->getConfigurations());
        }
        
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
            $response = $this->container->get('router')->handle($request);

            // Clear Doctrine cache
            /** @var EntityManager $em */
            $em = $this->container->get('doctrine')->getManager();
            $em->getUnitOfWork()->clear();
        } catch (\Exception $e) {
            $errorBody = sprintf('Uncaught "%s" with message "%s" in file "%s" on line %s',
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
        $this->container->get('command')->run();
    }
}