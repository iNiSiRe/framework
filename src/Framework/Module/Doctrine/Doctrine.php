<?php

namespace Framework\Module\Doctrine;

use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Framework\DependencyInjection\Container\Service;

class Doctrine extends Service
{
    protected $requiredParameters = ['driver', 'user', 'password', 'dbname', 'cache'];

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

    public function initialize()
    {
        if ($this->configuration->get('cache')) {
            $memcached = $this->container->get('memcached');
            $cache = new MemcachedCache();
            $cache->setMemcached($memcached);
        } else {
            $cache = null;
        }

        $config = Setup::createAnnotationMetadataConfiguration([ROOT_DIR . "/src"], true, ROOT_DIR . '/cache/doctrine/proxy', $cache);

        $connection = array(
            'driver'   => $this->configuration->get('driver'),
            'user'     => $this->configuration->get('user'),
            'password' => $this->configuration->get('password'),
            'dbname'   => $this->configuration->get('dbname'),
        );

        $this->entityManager = EntityManager::create($connection, $config);
    }
}