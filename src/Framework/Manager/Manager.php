<?php

namespace Framework\Manager;


class Manager
{
    /**
     * @var \PDOStatement
     */
    private $statement;

    public function __construct()
    {
        $database = [];
        $dsn = sprintf('mysql:dbname=%s;host=%s', $database['name'], $database['host']);
        $this->connection = new \PDO($dsn, $database['user'], $database['password']);
    }

    /**
     * @param $query
     * @param $parameters
     *
     * @return array
     */
    public function executeQuery($query, $parameters = [])
    {
        $this->statement = $this->connection->prepare($query);
        $this->statement->execute($parameters);
    }

    public function getResult()
    {
        return $this->statement->fetchAll();
    }
}