<?php

namespace Framework\Core\Database;

use Framework\Core\DependencyInjection\AbstractContainerService;
use Framework\Core\DependencyInjection\Container;

class Migrations extends AbstractContainerService
{
    /**
     * @var string
     */
    private $migrationsDir;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var Manager
     */
    private $manager;

    public function __construct(Container $container)
    {
        parent::__constructor($container);

        $this->manager = $this->container->get('database.manager');

        $config = $this->container->getParameter('migrations');

        if (!$this->isConfigValid($config)) {
            throw new \Exception('Bad migrations config.');
        }

        $this->applyConfig($config);
        $this->prepareEnvironment();
        $this->migrations = $this->loadMigrations();
    }

    /**
     * @param $config
     *
     * @return bool
     */
    private function isConfigValid($config)
    {
        return (isset($config['directoryPath'])
            && isset($config['tableName'])
        );
    }

    public function loadMigrations()
    {
        $migrations = array_filter(scandir($this->migrationsDir), function ($filename) {
            if (!preg_match('@^\d+\.(sql|php)$@', $filename)) {
                return false;
            }
            return true;
        });
        usort($migrations, function ($a, $b) {
            return (int)$a - (int)$b;
        });

        return $migrations;
    }

    /**
     * @param $config
     */
    private function applyConfig($config)
    {
        $this->migrationsDir = $config['directoryPath'];
        $this->tableName = $config['tableName'];
    }

    private function prepareEnvironment()
    {
        if (!is_dir($this->migrationsDir)) {
            mkdir($this->migrationsDir);
        }

        if (!$this->manager->executeQuery('DESCRIBE migration_versions')) {
            $this->manager->executeQuery('CREATE TABLE migration_versions (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, version VARCHAR(32), created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)');
        }
    }

    public function migrate()
    {

    }
}