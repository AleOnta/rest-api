<?php

namespace Src\System;

class DB
{
    private $connection = null;

    public function __construct()
    {
        # retrive env vars
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $db = $_ENV['DB_DATABASE'];
        $user = $_ENV['DB_USERNAME'];
        $pass = $_ENV['DB_PASSWORD'];

        # attempt db connection
        try {
            $this->connection = new \PDO(
                "pgsql:host={$host};port={$port};dbname={$db}",
                $user,
                $pass,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $e) {
            echo "\n----| Error while establishing db connection:";
            echo "\n    | --> msg: {$e->getMessage()}\n";
            die();
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function __destruct()
    {
        if ($this->connection) {
            $this->connection = null;
        }
    }
}
