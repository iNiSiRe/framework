<?php

namespace Framework\Module\Twig;

use Framework\DependencyInjection\Container\ServiceBuilder;
use Framework\Exception\ClassNotExistsException;
use Framework\Module\Twig\Exception\BadExtensionException;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;

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
        $twigLoader = new \Twig_Loader_Filesystem([
            APPLICATION_DIR,
            ROOT_DIR . '/vendor/inisire/framework/src',
            ROOT_DIR . '/vendor/symfony/twig-bridge/Resources/views/Form'
        ]);

        $twig = new \Twig_Environment($twigLoader, [
            'cache' => ROOT_DIR . '/cache/twig',
            'debug' => true
        ]);

        $translator = $this->container->get('translator');

        $formEngine = new TwigRendererEngine(array('Framework/Module/Administration/View/form_theme.html.twig'));
        $formEngine->setEnvironment($twig);
        $twig->addExtension(new FormExtension(new TwigRenderer($formEngine)));
        $twig->addExtension(new TranslationExtension($translator));

        // Register custom extensions
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