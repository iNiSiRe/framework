<?php

namespace Framework\Module\Translator\Extension;

use Framework\Module\Twig\Extension;
use Symfony\Component\Translation\Translator;

class TranslatorExtension extends Extension
{
    public function getFunctions()
    {
        /** @var Translator $translator */
        $translator = $this->container->get('translator');

        return [
            new \Twig_SimpleFunction('translate', [$translator, 'trans'])
        ];
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        /** @var Translator $translator */
        $translator = $this->container->get('translator');

        return [
            new \Twig_SimpleFilter('translate', [$translator, 'trans'])
        ];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'translator_extension';
    }
}