<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 06.05.15
 * Time: 14:27
 */

namespace Framework\Module\Router\Listener;

use Doctrine\ORM\EntityManager;
use Framework\DependencyInjection\Container\Service;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Kernel;
use Framework\Module\HttpServer\Event\RequestEvent;
use Framework\Module\Router\Exception\AccessDeniedException;
use Framework\Module\Router\Exception\NotFoundException;

class RequestEventListener extends Service
{
    /**
     * @param RequestEvent $event
     *
     * @return Response
     * @throws \Exception
     */
    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        try {
            $twig = $this->container->get('twig');
            $twig->addGlobal('request', $request);
            $this->recognizeLocale($request);
            $response = $this->container->get('router')->handle($request);

            // Clear Doctrine cache
            /** @var EntityManager $em */
//            $em = $this->container->get('doctrine')->getManager();
//            $em->getUnitOfWork()->clear();
        } catch (\Exception $e) {

            switch (true) {
                case $e instanceof NotFoundException:
                    $content = $this->container->get('twig')->render('Application\Common\Template\404.html.twig');
                    $response = new Response($content, ['Content-Type' => 'text/html'], 404);
                    break;

                case $e instanceof AccessDeniedException:
                    $response = new Response('Access denied', ['Content-Type' => 'text/html'], 403);
                    break;

                default:
                    $environment = $this->container->parameters->get('kernel.env');
                    if ($environment == Kernel::ENV_DEV_NAME) {
                        $error = sprintf('Uncaught "%s" with message "%s" in file "%s" on line %s',
                            get_class($e),
                            $e->getMessage(),
                            $e->getFile(),
                            $e->getLine()
                        );
                        $response = new Response($error, ['Content-Type' => 'text/html'], 500);
                    } else {
                        $content = $this->container->get('twig')->render('Application\Common\Template\500.html.twig');
                        $response = new Response($content, ['Content-Type' => 'text/html'], 500);
                    }
            }
        }

        $request->emit('response', [$response]);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function recognizeLocale(Request $request)
    {
        $locales = $this->container->parameters->get('locales', []);
        if (empty($locales)) {
            return false;
        }
        $string = implode('|', $locales);
        if (preg_match("#^/({$string})/?#", $request->getUrl(), $matches)) {
            $locale = $matches[1];
            $request->setUrl(preg_replace("#^/{$matches[1]}/?#", '/', $request->getUrl(), 1));
        } else {
            $locale = $locales[0];
        }
        $request->setLocale($locale);
        $this->container->get('translator')->setLocale($locale);

        return true;
    }
}