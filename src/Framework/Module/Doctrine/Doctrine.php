<?php

namespace Framework\Module\Doctrine;

use Application\Common\Subscriber\DoctrineSubscriber;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\ORM\Tools\Setup;
use Framework\DependencyInjection\Container\Service;
use Framework\Module\Console\Console;

class Doctrine extends Service
{
    protected $requiredParameters = ['driver', 'user', 'password', 'dbname', 'cache', 'host'];

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
        $this->initializeEntityManager();
//        $this->initializeCommands();
    }

    private function initializeEntityManager()
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
            'host' => $this->configuration->get('host')
        );

        $this->entityManager = EntityManager::create($connection, $config);

        $eventManager = $this->entityManager->getEventManager();
        $eventManager->addEventSubscriber(new DoctrineSubscriber());
    }

    private function initializeCommands()
    {
        /** @var Console $console */
        $console = $this->container->get('console');

//        DoctrineCommandHelper::setApplicationEntityManager($console->getApplication(), null);

        $helperSet = $console->getApplication()->getHelperSet();
        $helperSet->set(new ConnectionHelper($this->getManager()->getConnection()), 'db');
        $helperSet->set(new EntityManagerHelper($this->getManager()), 'em');

        $console->getApplication()->addCommands([
            new UpdateCommand(),
            new CreateCommand(),
            new DropCommand()
        ]);
    }
}