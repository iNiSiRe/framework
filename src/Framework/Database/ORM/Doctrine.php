<?php
/**
 * Created by PhpStorm.
 * User: Pride
 * Date: 3/21/2015
 * Time: 10:53 PM
 */

namespace Framework\Database\ORM;

use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Framework\DependencyInjection\Container\Container;
use Framework\DependencyInjection\Container\Service;

class Doctrine extends Service
{
    protected $configSection = 'doctrine';

    protected $requiredParameters = ['driver', 'user', 'password', 'dbname'];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @return EntityManager
     */
    public function getManager()
    {
        return $this->entityManager;
    }

    public function __construct(Container $container, array $configuration)
    {
        parent::__construct($container, $configuration);

        $isCacheEnabled = isset($this->configuration['cache']) ? $this->configuration['cache'] : false;
        if ($isCacheEnabled) {
            $memcached = $this->container->get('memcached');
            $cache = new MemcachedCache();
            $cache->setMemcached($memcached);
        } else {
            $cache = null;
        }

        $config = Setup::createAnnotationMetadataConfiguration([ROOT_DIR . "/src"], true, ROOT_DIR . '/cache/doctrine/proxy', $cache);

        $connection = array(
            'driver'   => $this->configuration['driver'],
            'user'     => $this->configuration['user'],
            'password' => $this->configuration['password'],
            'dbname'   => $this->configuration['dbname'],
        );

        $this->entityManager = EntityManager::create($connection, $config);
    }
}