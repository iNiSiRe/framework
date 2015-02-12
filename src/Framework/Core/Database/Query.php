<?php
/**
 * Created by PhpStorm.
 * User: Pride
 * Date: 2/11/2015
 * Time: 9:19 PM
 */

namespace Framework\Core\Database;


class Query
{
    /**
     * @var \PDOStatement
     */
    private $statement;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @param \PDOStatement $statement
     */
    public function __construct(\PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function execute()
    {
        $result = $this->statement->execute($this->parameters);

        if (!$result) {
            $error = $this->statement->errorInfo();
            throw new \Exception($error[2]);
        }

        return $result;
    }

    public function getResult()
    {
        if (!$this->execute()) {
            $error = $this->statement->errorInfo();
            throw new \Exception($error[2]);
        }

        return $this->statement->fetch(\PDO::FETCH_ASSOC);
    }
}