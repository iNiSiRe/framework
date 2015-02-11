<?php

namespace Framework\Core\Database\Command;

use Framework\Core\DependencyInjection\AbstractCommand;

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
        $this->container->get('database.migrations')->status();
    }
}