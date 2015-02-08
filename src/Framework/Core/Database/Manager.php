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
     * @param array $parameters
     *
     * @throws \Exception
     *
     * @return array
     */
    public function executeQuery($query, $parameters = [])
    {
        $this->statement = $this->connection->prepare($query);
        $result = $this->statement->execute($parameters);

        if (!$result) {
            $error = $this->statement->errorInfo();
            throw new \Exception($error[2]);
        }

        return $result;
    }

    public function getResult()
    {
        return $this->statement->fetchAll();
    }
}