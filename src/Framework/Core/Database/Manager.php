<?php

namespace Framework\Core\Database;

use Framework\Core\DependencyInjection\AbstractContainerService;
use Framework\Core\DependencyInjection\Container;

/**
 * Class Manager
 *
 * @package Framework\Core\Database
 */
class Manager extends AbstractContainerService
{
    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @var \PDOStatement
     */
    private $statement;

    public function __construct(Container $container)
    {
        parent::__constructor($container);

        $config = $this->container->getParameter('database');
        $dsn = sprintf('mysql:dbname=%s;host=%s', $config['name'], $config['host']);
        $this->connection = new \PDO($dsn, $config['user'], $config['password']);
    }

    /**
     * @param $query
     *
     * @return Query
     */
    public function createQuery($query)
    {
        return new Query($this->connection->prepare($query));
    }

    public function executeQuery($query)
    {
        $query = new Query($this->connection->prepare($query));

        return $query->execute();
    }
}