<?php

namespace Framework\Database\Command;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Framework\DependencyInjection\AbstractCommand;

class MigrateCommand extends AbstractCommand
{
    /**
     * @param $argvInput
     */
    public function migrate($argvInput)
    {
        $this->container->get('database.migrations')->migrate();
    }

    /**
     * @param $argvInput
     */
    public function status($argvInput)
    {
        list ($currentMigrations, $availableMigrations) = $this->container->get('database.migrations')->status();

        echo PHP_EOL;
        echo sprintf('Available migrations: %s' . PHP_EOL, count($availableMigrations));
        echo sprintf('Executed migrations: %s' . PHP_EOL, count($currentMigrations));
        echo PHP_EOL;
        echo sprintf('Current version: %s' . PHP_EOL, count($currentMigrations) != 0 ? end($currentMigrations) : 0);
        echo sprintf('New migrations: %s' . PHP_EOL, count($availableMigrations) - count($currentMigrations));
        echo PHP_EOL;
    }

    /**
     * @param $argvInput
     */
    public function generate($argvInput)
    {
        $fileName = $this->container->get('database.migrations')->generate();
        echo PHP_EOL;
        echo sprintf('Generated empty migration "%s"', $fileName);
        echo PHP_EOL;
    }

    public function schema($argvInput)
    {
        $entityManager = $this->container->get('doctrine.manager');
        $helperSet = ConsoleRunner::createHelperSet($entityManager);
        $_SERVER['argv'][1] = 'orm:schema-tool:update';
        ConsoleRunner::run($helperSet, []);
    }
}