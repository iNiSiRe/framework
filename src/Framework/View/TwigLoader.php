<?php
/**
 * Created by PhpStorm.
 * User: Pride
 * Date: 3/22/2015
 * Time: 11:00 PM
 */

namespace Framework\View;


use Framework\DependencyInjection\Container\ServiceLoader;

class TwigLoader extends ServiceLoader
{
    /**
     * @return mixed
     */
    public function load()
    {
        $twigLoader = new \Twig_Loader_Filesystem(APPLICATION_DIR);

        return new \Twig_Environment($twigLoader, [
            'cache' => ROOT_DIR . '/cache/twig',
            'debug' => true
        ]);
    }
}