<?php

namespace Framework\Module\Router\Extension;

use Framework\Module\Router\Router;
use Framework\Module\Twig\Extension;

class RouterExtension extends Extension
{
    public function getFunctions()
    {
        /** @var Router $router */
        $router = $this->container->get('router');

        return [
            new \Twig_SimpleFunction('generate_url', [$router, 'generateUrl']),
            new \Twig_SimpleFunction('isCurrentUrl', [$router, 'isCurrentUrl'])
        ];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'router_extension';
    }
}