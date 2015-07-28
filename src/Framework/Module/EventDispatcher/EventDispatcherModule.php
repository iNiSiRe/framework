<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 08.05.15
 * Time: 16:32
 */

namespace Framework\Module\EventDispatcher;

use Framework\Module;

class EventDispatcherModule extends Module
{
    public function getConfigurations()
    {
        return [__DIR__ . '/Configuration/configuration.yml'];
    }
}