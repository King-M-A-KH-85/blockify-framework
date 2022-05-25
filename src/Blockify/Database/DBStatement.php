<?php
namespace Blockify\Database;

use PDO;
use PDOStatement;

class DBStatement
{
    private PDOStatement $stmt;

    public function __construct(PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    public function bind($param, $value): bool
    {
        return $this->stmt->bindValue($param, $value);
    }

    public function result_exec(): array
    {
        self::exec();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exec(): bool
    {
        return $this->stmt->execute();
    }

    public function rowCount(): int
    {
        self::exec();
        return $this->stmt->rowCount();
    }
}
