<?php

namespace Framework;

use Application\ApplicationModule;
use Composer\Autoload\ClassLoader;
use Framework\Configuration\ConfigurationLoader;
use Framework\DependencyInjection\Container\Container;
use Framework\Http\Request;
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
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->container->get('dispatcher');
        $dispatcher->dispatch(self::EVENT_REQUEST, []);

        $loop = Factory::create();
        $socket = new Server($loop);
        $http = new HttpServer($socket);
        $http->on('request', function (ReactRequest $reactRequest, ReactResponse $reactResponse) {

            $request = new Request(
                $reactRequest->getMethod(),
                $reactRequest->getPath(),
                $reactRequest->getQuery(),
                $reactRequest->getHeaders(),
                $reactRequest->getHttpVersion()
            );

            $reactRequest->on('data', function ($data) use ($request, $reactResponse) {
                $request->setBody($data);
                $response = $kernel->handleRequest($request);
                $reactResponse->writeHead($response->getStatusCode(), $response->getHeaders());
                $reactResponse->end($response->getBody());
            });
        });

        $socket->listen(8080, '0.0.0.0');
        echo 'Started';
        $loop->run();
    }

    public function runCommand()
    {
        $this->container->get('command')->run();
    }
}