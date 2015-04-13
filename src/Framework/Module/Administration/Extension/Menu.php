<?php

namespace Framework\Module\Administration\Extension;

use Framework\Module\Administration\Administration;
use Framework\Module\Router\Router;
use Framework\Module\Twig\Extension;

class Menu extends Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('administration_menu', [$this, 'menu'])
        ];
    }

    public function menu()
    {
        /**
         * @var Administration $cms
         * @var Router $router
         */
        $cms = $this->container->get('administration');
        $router = $this->container->get('router');

        $pages = $cms->getPages()->all();

        $items = [];
        foreach ($pages as $name => $page) {
            $items[] = [
                'name' => $name,
                'url' => $router->generateUrl('administration_list', [$name])
            ];
        }

        return $items;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'administration_menu';
    }
}