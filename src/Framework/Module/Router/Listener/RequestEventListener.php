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
            $response = $this->container->get('router')->handle($request);

            // Clear Doctrine cache
            /** @var EntityManager $em */
            $em = $this->container->get('doctrine')->getManager();
            $em->getUnitOfWork()->clear();
        } catch (\Exception $e) {

            switch (true) {
                case $e instanceof NotFoundException:
                    $response = new Response('Not found', ['Content-Type' => 'text/html'], 404);
                    break;

                case $e instanceof AccessDeniedException:
                    $response = new Response('Access denied', ['Content-Type' => 'text/html'], 403);
                    break;

                default:
                    $errorBody = sprintf('Uncaught "%s" with message "%s" in file "%s" on line %s',
                        get_class($e),
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    );

                    $response = new Response($errorBody, ['Content-Type' => 'text/html'], 500);
            }
        }

        $request->emit('response', [$response]);
    }
}