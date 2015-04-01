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
use Framework\DependencyInjection\Container\ServiceLoader;
use Framework\Foundation\Dictionary;

class Doctrine extends ServiceLoader
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

    /**
     * @return Dictionary
     */
    private function getConfiguration()
    {
        $configuration = $this->container->getConfiguration($this->configSection);
        foreach ($this->requiredParameters as $required) {
            if (!isset($configuration[$required])) {
                throw new \InvalidArgumentException('Service "%s" has required configuration parameter "%s"', get_class($this), $required);
            }
        }

        return new Dictionary($configuration);
    }

    /**
     * @return mixed
     */
    public function load()
    {
        $configuration = $this->getConfiguration();

        $isCacheEnabled = $configuration->get('cache');
        if ($isCacheEnabled) {
            $memcached = $this->container->get('memcached');
            $cache = new MemcachedCache();
            $cache->setMemcached($memcached);
        } else {
            $cache = null;
        }

        $config = Setup::createAnnotationMetadataConfiguration([ROOT_DIR . "/src"], true, ROOT_DIR . '/cache/doctrine/proxy', $cache);

        $connection = array(
            'driver'   => $configuration->get('driver'),
            'user'     => $configuration->get('user'),
            'password' => $configuration->get('password'),
            'dbname'   => $configuration->get('dbname'),
        );

        $this->entityManager = EntityManager::create($connection, $config);

        return $this;
    }
}