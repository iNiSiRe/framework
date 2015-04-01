<?php

namespace Framework\Database;

use Application\User\Entity\Entity;
use Framework\DependencyInjection\ContainerService;
use Framework\DependencyInjection\Container;
use PDO;

/**
 * Class Manager
 *
 * @package Framework\Core\Database
 */
class Manager extends ContainerService
{
    /**
     * @param Container $container
     * @param array     $config
     *
     * @throws \Exception
     */
    public function __construct(Container $container, array $config)
    {
        parent::__construct($container, $config);
    }
}