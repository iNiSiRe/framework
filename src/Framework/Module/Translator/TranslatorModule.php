<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 05.05.15
 * Time: 20:49
 */

namespace Framework\Module\Translator;


use Framework\Module;

class TranslatorModule extends Module
{
    public function getConfigurations()
    {
        return [__DIR__ . '/Configuration/configuration.yml'];
    }
}