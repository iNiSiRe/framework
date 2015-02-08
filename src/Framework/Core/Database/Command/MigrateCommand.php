<?php

namespace Framework\Core\Database\Command;

use Framework\Core\DependencyInjection\AbstractCommand;

class MigrateCommand extends AbstractCommand
{
    /**
     * @param $argvInput
     */
    public function execute($argvInput)
    {
        $this->container->get('database.migrations')->migrate();
    }
}