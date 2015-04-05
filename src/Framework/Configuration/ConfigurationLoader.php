<?php

namespace Framework\Configuration;

use Symfony\Component\Yaml\Yaml;

class ConfigurationLoader
{
    private $files = [];

    public function addFiles($files)
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }
    }

    public function addFile($file)
    {
        if (!file_exists($file)) {
            throw new \Exception();
        }

        if (in_array($file, $this->files)) {
            return;
        }

        $this->files[] = $file;
    }

    public function load()
    {
        $configuration = [];
        foreach ($this->files as $file) {
            $content = file_get_contents($file);
            $part =  Yaml::parse($content);
            $configuration = $this->merge($configuration, $part);
        }

        return $configuration;
    }

    private function merge($configuration, $part)
    {
        return array_replace_recursive($configuration, $part);
    }
}