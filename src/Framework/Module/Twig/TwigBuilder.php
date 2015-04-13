<?php

namespace Framework\Module\Twig;

use Framework\DependencyInjection\Container\ServiceBuilder;
use Framework\Exception\ClassNotExistsException;
use Framework\Module\Twig\Exception\BadExtensionException;

class TwigBuilder extends ServiceBuilder
{
    /**
     * @return mixed
     *
     * @throws BadExtensionException
     * @throws ClassNotExistsException
     */
    public function build()
    {
        $twigLoader = new \Twig_Loader_Filesystem([APPLICATION_DIR, __DIR__ . '/../../../']);

        $twig = new \Twig_Environment($twigLoader, [
            'cache' => ROOT_DIR . '/cache/twig',
            'debug' => true
        ]);

        // Register extensions
        $extensions = $this->container->configuration->get('extensions', []);
        foreach ($extensions as $class) {
            if (!class_exists($class)) {
                throw new ClassNotExistsException($class);
            }

            $instance = new $class;

            if (!$instance instanceof Extension) {
                throw new BadExtensionException($class);
            }

            $instance->setContainer($this->container);

            $twig->addExtension($instance);
        }

        return $twig;
    }
}