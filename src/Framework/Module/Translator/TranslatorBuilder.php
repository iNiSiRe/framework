<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 05.05.15
 * Time: 19:07
 */

namespace Framework\Module\Translator;

use Framework\DependencyInjection\Container\ServiceBuilder;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

class TranslatorBuilder extends ServiceBuilder
{
    /**
     * @var Translator
     */
    private $translator;

    public function initialize()
    {
        $translator = new Translator('ua');
        $translator->addLoader('yaml', new YamlFileLoader());

        $translations = $this->container->configuration->get('translations', []);

        foreach ($translations as $name => $definition) {
            $path = sprintf('%s/%s', APPLICATION_DIR, $definition);
            $translator->addResource('yaml', $path, $name);
        }

        $this->translator = $translator;
    }

    public function build()
    {
        return $this->translator;
    }
}