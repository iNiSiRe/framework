<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 30.07.15
 * Time: 21:29
 */

namespace Framework\Module\EventDispatcher;


class AsynchronousDispatcher
{
    private $running = false;

    private $queue;

    public function __construct()
    {

    }
}