<?php

namespace Framework\Controller;

use Framework\DependencyInjection\ContainerService;

/**
 * Class Controller
 *
 * @package Framework\Core
 */
class Controller extends ContainerService
{
    public function render($template, $context = [])
    {
        return $this->container->get('twig')->render($template, $context);
    }
}