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
        return array_key_exists($key, $this->dictionary) ? $this->dictionary[$key] : $default;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->dictionary);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->dictionary[$key] = $value;
    }

    /**
     * @param array $items
     */
    public function add(array $items)
    {
        $this->dictionary = array_merge($this->dictionary, $items);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->dictionary;
    }
}