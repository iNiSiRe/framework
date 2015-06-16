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
use Framework\Module\Router\Exception\AccessDeniedException;
use Framework\Module\Router\Exception\NotFoundException;

class RequestEventListener extends Service
{
    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function onRequest(Request $request)
    {
        try {
            $this->recognizeLocale($request);
            $response = $this->container->get('router')->handle($request);

            // Clear Doctrine cache
            /** @var EntityManager $em */
            $em = $this->container->get('doctrine')->getManager();
            $em->getUnitOfWork()->clear();
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

                    $error = sprintf('Uncaught "%s" with message "%s" in file "%s" on line %s',
                        get_class($e),
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    );

                    $content = $this->container->get('twig')->render('Application\Common\Template\500.html.twig', compact('error'));

                    $response = new Response($content, ['Content-Type' => 'text/html'], 500);
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
        $locales = implode('|', $locales);
        if (preg_match("#^/({$locales})/#", $request->getUrl(), $matches)) {
            $locale = $matches[1];
            $request->setUrl(preg_replace("#^/{$matches[1]}/#", '/', $request->getUrl(), 1));
        } else {
            $locale = $locales[0];
        }
        $request->setLocale($locale);
        $this->container->get('translator')->setLocale($locale);

        return true;
    }
}