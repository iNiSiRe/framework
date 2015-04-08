<?php

namespace Framework\View;

use Framework\DependencyInjection\Container\ServiceBuilder;

class TwigBuilder extends ServiceBuilder
{
    /**
     * @return mixed
     */
    public function build()
    {
        $twigLoader = new \Twig_Loader_Filesystem(APPLICATION_DIR);

        return new \Twig_Environment($twigLoader, [
            'cache' => ROOT_DIR . '/cache/twig',
            'debug' => true
        ]);
    }
}