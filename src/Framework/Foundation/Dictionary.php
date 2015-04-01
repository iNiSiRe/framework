<?php

namespace Framework\Foundation;

class Dictionary
{
    /**
     * @var array
     */
    private $dictionary;

    /**
     * @param array $dictionary
     */
    public function __construct(array $dictionary = [])
    {
        $this->dictionary = $dictionary;
    }

    /**
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->dictionary[$key]) ? $this->dictionary[$key] : $default;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->dictionary[$key] = $value;
    }
}