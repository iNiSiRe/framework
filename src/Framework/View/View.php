<?php
/**
 * Created by PhpStorm.
 * User: Pride
 * Date: 2/28/2015
 * Time: 2:24 PM
 */

namespace Framework\View;


class View implements RendererInterface
{
    protected $templatePath;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function setTemplate($templatePath)
    {
        $this->templatePath = $templatePath;
    }

    public function render()
    {
        // TODO: Implement render() method.
    }
}