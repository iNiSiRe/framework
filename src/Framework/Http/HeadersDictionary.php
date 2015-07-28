<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 19.05.15
 * Time: 13:36
 */

namespace Framework\Http;


use Framework\Foundation\Dictionary;

class HeadersDictionary extends Dictionary
{
    public function __construct(array $dictionary = [])
    {
        $caseInsensitiveDictionary = [];
        foreach ($dictionary as $key => $value) {
            $caseInsensitiveDictionary[mb_strtolower($key)] = $value;
        }
        parent::__construct($caseInsensitiveDictionary);
    }

    public function get($key, $default = null)
    {
        return parent::get(mb_strtolower($key), $default);
    }
}