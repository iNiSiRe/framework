<?php
/**
 * Created by PhpStorm.
 * User: Pride
 * Date: 2/8/2015
 * Time: 12:36 PM
 */

namespace Framework\DependencyInjection\Container;

interface ServiceInterface
{
    public function setContainer(Container $container);
}