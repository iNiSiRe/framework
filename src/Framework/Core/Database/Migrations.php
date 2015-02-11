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
    }

    private function getCurrentMigrations()
    {
        $query = $this->manager->createQuery('SELECT version FROM migration_versions ORDER BY created_at');
        return array_values($query->getResult());
    }

    public function status()
    {
        $availableMigrations = $this->getAvailableMigrations();
        $currentMigrations = $this->getCurrentMigrations();

        echo sprintf('Available migrations: %s %s', count($availableMigrations), PHP_EOL);
        echo sprintf('Executed migrations: %s %s', count($currentMigrations), PHP_EOL);
        echo sprintf('Current version: %s %s', end($currentMigrations), PHP_EOL);
    }

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

    private function applyMigration($query, $name)
    {
        $migrationQuery = $this->manager->createQuery($query);
        $migrationQuery->getResult();

        $addVersionQuery = $this->manager->createQuery('INSERT INTO migration_versions (version) VALUES (:name)');
        $addVersionQuery->setParameters([':name' => $name])
            ->getResult();
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

    private function prepareEnvironment()
    {
        if (!is_dir($this->migrationsDir)) {
            mkdir($this->migrationsDir);
        }

        if (!$this->manager->createQuery('DESCRIBE migration_versions')) {
            $this->manager->createQuery('CREATE TABLE migration_versions (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, version VARCHAR(32) UNIQUE, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)');
        }
    }
}