<?php

namespace Blockify\Database;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;

    public function __construct(string $host, string $dbName, string $username, string $password)
    {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbName", $username, $password, [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function query(string $query): DBStatement
    {
        return new DBStatement($this->pdo->prepare($query));
    }
}
