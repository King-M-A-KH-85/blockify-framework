<?php
namespace Blockify\Database;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;

    public function __construct()
    {
        try {
            $this->pdo = new PDO("mysql:host=localhost;dbname=ThLearn", "root", "", [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function query(String $query): DBStatement
    {
        return new DBStatement($this->pdo->prepare($query));
    }
}
