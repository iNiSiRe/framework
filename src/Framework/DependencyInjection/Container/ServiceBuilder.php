<?php
/**
 * Created by PhpStorm.
 * User: Pride
 * Date: 3/24/2015
 * Time: 1:25 AM
 */

namespace Framework\DependencyInjection\Container;

abstract class ServiceBuilder extends Service
{
    abstract public function build();
}