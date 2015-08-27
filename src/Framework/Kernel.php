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
use Framework\Module\Redis\RedisModule;
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
    const ENV_DEV = 1;
    const ENV_PROD = 2;

    const ENV_DEV_NAME = 'dev';
    const ENV_PROD_NAME = 'prod';

    protected static $environmentLabels = [
        self::ENV_DEV => self::ENV_DEV_NAME,
        self::ENV_PROD => self::ENV_PROD_NAME
    ];

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Module[]
     */
    protected $modules;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param     $environment
     * @param     $rootDir
     */
    public function __construct($environment, $rootDir)
    {
        $this->environment = $environment;
        $this->rootDir = $rootDir;
        $this->modules = [];

        // Enable modules
        $this->addModules([
            new RedisModule(),
            new EventDispatcherModule(),
            new HttpServerModule(),
            new MemcachedModule(),
            new RouterModule(),
            new DoctrineModule(),
            new TranslatorModule(),
            new TwigModule(),
            new ConsoleModule(),
            new AdministrationModule()
        ]);
    }

    /**
     * Load modules
     */
    public function load()
    {
        $loader = new ConfigurationLoader();

        // Load modules configurations
        foreach ($this->modules as $module) {
            $loader->addFiles($module->getConfigurations());
        }

        $this->container = new Container($loader->load());

        // Define kernel parameters
        $this->container->parameters->set('kernel.root_dir', $this->rootDir);
        $this->container->parameters->set('kernel.env', self::getEnvironmentName($this->environment));

        // Compile container
        $this->container->compile();
    }

    public function run()
    {
        $server = $this->container->get('http_server');
        $server->run();
    }

    /**
     * @param int $environment
     *
     * @return string
     */
    public static function getEnvironmentName($environment)
    {
        return self::$environmentLabels[$environment];
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param array $modules
     */
    public function addModules(array $modules)
    {
        $this->modules = array_merge($this->modules, $modules);
    }
}