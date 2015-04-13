<?php

namespace Framework\Module\Administration;

use Framework\DependencyInjection\Container\Service;
use Framework\Foundation\Dictionary;

class Administration extends Service
{
    /**
     * @var Dictionary
     */
    private $pages;

    public function __construct()
    {
        $this->pages = new Dictionary();
    }

    public function initialize()
    {
        $pages = $this->container->configuration->get('administration_pages', []);

        foreach ($pages as $name => $class) {
            $this->pages->set($name, new $class);
        }
    }

    public function get($name)
    {
        return $this->pages->get($name);
    }

    public function getPages()
    {
        return $this->pages;
    }
}