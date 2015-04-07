<?php

namespace Framework\Controller;

use Framework\DependencyInjection\Container\Service;

/**
 * Class Controller
 *
 * @package Framework\Core
 */
class Controller extends Service
{
    public function render($template, $context = [])
    {
        return $this->container->get('twig')->render($template, $context);
    }
}