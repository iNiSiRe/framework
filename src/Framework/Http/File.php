<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 28.05.15
 * Time: 22:27
 */

namespace Framework\Http;

class File
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $originalName;

    /**
     * @param string $name
     * @param string $originalName
     */
    public function __construct($name = '', $originalName = '')
    {
        $this->name = $name;
        $this->originalName = $originalName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @param string $originalName
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;
    }
}