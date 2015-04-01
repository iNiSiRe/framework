<?php

namespace Framework\View;

use Composer\Autoload\ClassLoader;
use Framework\DependencyInjection\ContainerService;

class ViewBuilder extends ContainerService
{
    /**
     * @param string $templatePath
     * @param array  $parameters
     *
     * @throws \Exception
     *
     * @return string
     */
    public function render($templatePath, $parameters = [])
    {
        /**
         * @var ClassLoader $classLoader
         */

        foreach ($parameters as $key => $value) {
            $$key = $value;
        }

        $classLoader = $this->container->get('kernel')->getLoader();

        $pathInfo = pathinfo($templatePath);
        $template = sprintf('%s/%s', $pathInfo['dirname'], $pathInfo['filename']);
        $file = $classLoader->findFile($template);

        ob_start();
        include $file;
        $renderedView = ob_get_clean();

        return $renderedView;
    }
}