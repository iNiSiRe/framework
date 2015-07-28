<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 09.05.15
 * Time: 21:30
 */

namespace Framework\Module\HttpServer;

use Framework\Module;

class HttpServerModule extends Module
{
    public function getConfigurations()
    {
        return [__DIR__ . '/Configuration/configuration.yml'];
    }
}