<?php
/**
 * Created by PhpStorm.
 * User: Pride
 * Date: 2/28/2015
 * Time: 2:17 PM
 */

namespace Framework\View;


abstract class ViewDecorator implements RendererInterface
{
    /**
     * @var RendererInterface
     */
    protected $wrapped;

    public function __construct(RendererInterface $wrappable)
    {
        $this->wrapped = $wrappable;
    }
}