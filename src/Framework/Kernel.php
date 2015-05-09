<?php

namespace Framework;

use Application\ApplicationModule;
use Composer\Autoload\ClassLoader;
use Framework\Configuration\ConfigurationLoader;
use Framework\DependencyInjection\Container\Container;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Module\EventDispatcher\EventDispatcherModule;
use Framework\Module\HttpServer\HttpServerModule;
use React\EventLoop\Factory;
use React\Http\Request as ReactRequest;
use React\Http\Response as ReactResponse;
use Framework\Module\Administration\AdministrationModule;
use Framework\Module\Console\ConsoleModule;
use Framework\Module\Doctrine\DoctrineModule;
use Framework\Module\EventDispatcher\EventDispatcher;
use Framework\Module\Memcached\MemcachedModule;
use Framework\Module\Router\RouterModule;
use Framework\Module\Translator\TranslatorModule;
use Framework\Module\Twig\TwigModule;
use React\Http\Server as HttpServer;
use React\Socket\Server;

class Kernel
{
    const MODE_DEFAULT = 1;
    const MODE_CONSOLE = 2;

    const ENV_DEV = 1;
    const ENV_PROD = 2;

    const EVENT_REQUEST = 'request';

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
            new EventDispatcherModule(),
            new HttpServerModule(),
            new MemcachedModule(),
            new RouterModule(),
            new DoctrineModule(),
            new TranslatorModule(),
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

    public function run()
    {
        $server = $this->container->get('http_server');
        $server->run();
    }
}