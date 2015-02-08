<?php
/**
 * Created by PhpStorm.
 * User: Pride
 * Date: 2/8/2015
 * Time: 12:36 PM
 */

namespace Framework\Core\DependencyInjection;

interface ContainerServiceInterface
{
    /**
     * All container's services should have this method
     *
     * @param Container $container
     *
     * @return void
     */
    public function setContainer(Container $container);
}