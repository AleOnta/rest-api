<?php

namespace Src\Gateways;

abstract class TableGateway
{
    protected string $table;
    protected \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    abstract public function insert($entity);
    abstract public function update($entity);
    abstract public function delete($entity);
    abstract public function find(array $queryParams);
    abstract public function findById(int $id);
    abstract public function findAll();
}
