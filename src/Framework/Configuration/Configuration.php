<?php

namespace Framework\Configuration;

use Symfony\Component\Yaml\Yaml;

class Configuration
{
    private $files = [];
    private $sections = [];
    private $configuration;

    public function addFile($file)
    {
        if (!file_exists($file)) {
            throw new \Exception();
        }

        if (in_array($file, $this->files)) {
            return;
        }

        $this->files = $file;
    }

    public function load()
    {
        foreach ($this->files as $file) {
            $content = file_get_contents($file);
            $configuration =  Yaml::parse($content);
            $this->addConfiguration($configuration);
        }
    }

    public function addFiles($files)
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }
    }

    private function addConfiguration($configuration)
    {
        if (isset($configuration['imports'])) {
            $this->addFiles($configuration['imports']);
            unset($configuration['imports ']);
        }

        $this->addSections(array_keys($configuration));

        foreach ($this->getSections() as $section) {
            $this->configuration[$section] = array_merge(
                isset($this->configuration[$section]) ? $this->configuration[$section] : [],
                isset($configuration[$section]) ? $configuration[$section] : []
            );
        }
    }

    /**
     * @param $sections
     */
    private function addSections(array $sections)
    {
        $this->sections = array_merge($this->sections, $sections);
    }

    /**
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @param $section
     *
     * @return array
     */
    public function get($section)
    {
        return isset($this->configuration[$section]) ? $this->configuration[$section] : [];
    }
}