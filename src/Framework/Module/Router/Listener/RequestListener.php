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
}