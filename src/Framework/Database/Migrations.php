<?php

namespace Framework\Database;

use Framework\DependencyInjection\ContainerService;
use Framework\DependencyInjection\Container;

/**
 * Database migrations logic
 *
 * Class Migrations
 *
 * @package Framework\Core\Database
 */
class Migrations extends ContainerService
{
    /**
     * @var string
     */
    private $migrationsDir;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * Check config and prepare environment
     *
     * @param Container $container
     *
     * @throws \Exception
     */
    public function __construct(Container $container, array $config)
    {
        parent::__construct($container, $config);

        $this->manager = $this->container->get('database.manager');

        $config = $this->container->getParameter('migrations');

        if (!$this->isConfigValid($config)) {
            throw new \Exception('Bad migrations config.');
        }

        $this->applyConfig($config);
        $this->prepareEnvironment();
        $this->container = $container;
    }

    /**
     * Check migrations status
     *
     * @return array
     */
    public function status()
    {
        $availableMigrations = $this->getAvailableMigrations();
        $currentMigrations = $this->getCurrentMigrations();

        return [$currentMigrations, $availableMigrations];
    }

    /**
     * Complete migration
     */
    public function migrate()
    {
        $availableMigrations = $this->getAvailableMigrations();
        $currentMigrations = $this->getCurrentMigrations();

        foreach ($availableMigrations as $migration) {

            $migrationName = pathinfo($migration)['filename'];

            if (in_array($migrationName, $currentMigrations)) {
                continue;
            }

            echo $migration;
            $fileName = sprintf('%s/%s', $this->migrationsDir, $migration);
            $query = file_get_contents($fileName);
            $this->applyMigration($query, $migrationName);
        }
    }

    /**
     * Generate empty migration file
     *
     * @return string
     */
    public function generate()
    {
        $date = new \DateTime();
        $fileName = sprintf('%s/%s.sql', $this->migrationsDir, $date->format('Ymdhi'));
        $file = fopen($fileName, 'w');
        fclose($file);

        return $fileName;
    }

    /**
     * Execute migration and add version
     *
     * @param $query
     * @param $name
     *
     * @throws \Exception
     */
    private function applyMigration($query, $name)
    {
        //TODO: This queries should be executed in transaction

        $this->manager->getQuery($query)->execute();
        $this->manager->getQuery('INSERT INTO migration_versions (version) VALUES (:name)', [':name' => $name])->execute();
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

    /**
     * @return array
     *
     * @throws \Exception
     */
    private function getCurrentMigrations()
    {
        $query = $this->manager->getQuery('SELECT version FROM migration_versions ORDER BY created_at');
        $result = $query->getResult();

        return ($result !== false) ? array_values($result) : [];
    }

    /**
     * @return array
     */
    private function getAvailableMigrations()
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

    /**
     * Create dir and database table for migrations
     */
    private function prepareEnvironment()
    {
        if (!is_dir($this->migrationsDir)) {
            mkdir($this->migrationsDir);
        }

        $sql = 'CREATE TABLE IF NOT EXISTS migration_versions (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, version VARCHAR(32) UNIQUE, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)';

        $this->manager->getQuery($sql)->execute();
    }
}