<?php
namespace Blockify\Database;

use JetBrains\PhpStorm\Pure;

class DBUtil
{
    private Database $database;
    private string $table = "";

    private string $fields = "";
    private string $where = "";
    private mixed $dataList;

    const SELECT = 1;
    const INSERT = 2;
    const UPDATE = 3;
    const DELETE = 4;

    public function setTable(string $table): static
    {
        $this->table = trim($table);

        return $this;
    }

    public function setWhere(string $where): static
    {
        $this->where = $where;

        return $this;
    }

    public function __construct()
    {
        //create an instance of database classes
        $this->database = new Database();
    }

    #[Pure] private function parseData(array $data): string
    {
        $arrData = array();

        foreach ($data as $index => $value) {
            if (!is_numeric($index) && !empty($index) && !is_numeric($value) && !empty($value)) {
                $parseFieldName = $this->findFields($index);

                if (!empty($parseFieldName)) {
                    $arrData[] = $parseFieldName . "='" . $this->parseValue($value) . "'";
                } else {
                    $arrData[] = "`" . $this->parsekey($index) . "`='" . $this->parseValue($value) . "'";
                }
            }
        }

        $ret = (count($arrData) > 0) ? implode(",", $arrData) : "";

        $arrData = null;

        return $ret;
    }

    #[Pure] private function findFields(string $fields, string $table = ""): string
    {
        $ret = "";

        if (!empty($fields)) {
            $fields = trim($fields);
            $arrSecs = explode(" ", $fields);
            $secLength = count($arrSecs);

            $arrTables = explode(".", $arrSecs[0]);
            $tableLength = count($arrTables);
            if ($secLength == 3) {

                if ($tableLength == 2) {
                    $ret = "`" .
                        $this->parsekey($arrTables[0]) . "`.`" .
                        $this->parsekey($arrTables[1]) . "` " .
                        $this->parsekey($arrSecs[1]) . " `" .
                        $this->parsekey($arrSecs[2]) . "`";
                } else {
                    $ret .= (!empty($table)) ? "`" . $this->parsekey($table) . "`." : "";
                    $ret .= "`" .
                        $this->parsekey($arrTables[0]) . "` " .
                        $this->parsekey($arrSecs[1]) . " `" .
                        $this->parsekey($arrSecs[2]) . "`";
                }

            } else {

                if ($tableLength == 2) {
                    if ($secLength == 2) {
                        $ret = "`" .
                            $this->parsekey($arrTables[0]) . "`.`" .
                            $this->parsekey($arrTables[1]) . "` " .
                            $this->parsekey($arrSecs[1]);
                    } else {
                        $ret = "`" .
                            $this->parsekey($arrTables[0]) . "`.`" .
                            $this->parsekey($arrTables[1]) . "`";
                    }
                } else {
                    if ($secLength == 2) {
                        $ret .= (!empty($table)) ? "`" . $this->parsekey($table) . "`." : "";
                        $ret .= "`" .
                            $this->parsekey($arrTables[0]) . "` " .
                            $this->parsekey($arrSecs[1]);
                    }
                }

            }
            $arrTables = null;
            $arrSecs = null;
        }

        return $ret;
    }

    private function parseKey(string $key): string
    {
        return trim($key);
    }

    private function parseValue(string $value): string
    {
        return trim($value);
    }

    public function select(array|null $selectFields = null): array|bool
    {
        if ($selectFields != null) {
            $this->fields = self::parseFields($selectFields);
        }
        $sql = self::parseSQL(self::SELECT);
        $query = $this->database->query($sql);
        return $query->result_exec();
    }

    #[Pure] private function parseFields(array $fields): string
    {

        $arrFields = array();

        foreach ($fields as $keyA => $keyB) {
            if (!is_string($keyB) || empty($keyB)) {
                continue;
            }

            $parseFieldName = $this->findFields($keyB);

            if (!empty($parseFieldName)) {
                $arrFields[] = $parseFieldName;
            } else {
                if (is_string($keyA) && !empty($keyA)) {
                    $parseFieldName = $this->findFields($keyA, $keyB);

                    if (!empty($parseFieldName)) {
                        $arrFields[] = $parseFieldName;
                    } else {
                        $arrFields[] = "`" .
                            $this->parsekey($keyB) . "`.`" .
                            $this->parsekey($keyA) . "`";
                    }
                } else {
                    $arrFields[] = "`" . $this->parsekey($keyB) . "`";
                }
            }
        }

        $ret = (count($arrFields) > 0) ? implode(",", $arrFields) : "";

        $arrFields = null;

        return $ret;
    }

    private function parseSQL(int $action): string
    {
        $ret = "";

        $table = $this->table;
        $fields = $this->fields;
        $where = $this->where;

        switch ($action) {
            case self::SELECT:
                if (!empty($table)) {
                    $ret = "SELECT";
                    $ret .= (!empty($fields)) ? " " . $fields : " *";
                    $ret .= " FROM " . $table;
                    $ret .= (!empty($where)) ? " WHERE " . $where : "";
                }

                break;

            case self::INSERT:
                if (!empty($table) && !empty($this->dataList)) {
                    $ret = "INSERT INTO";
                    $ret .= " " . $table;
                    $ret .= " SET " . $this->dataList;
                }

                break;

            case self::UPDATE:
                if (!empty($table) && !empty($dataList)) {
                    $ret = "update";
                    $ret .= " " . $table;
                    $ret .= " set " . $dataList;
                    $ret .= (!empty($where)) ? " where " . $where : "";
                }

                break;

            case self::DELETE:
                if (!empty($table)) {
                    $ret = "delete";
                    $ret .= " from " . $table;
                    $ret .= (!empty($where)) ? " WHERE " . $where : "";
                }

                break;
        }

        return $ret;
    }

    public function insert(array $insertData): bool
    {
        $this->dataList = $this->parseData($insertData);
        $sql = $this->parseSQL(self::INSERT);
        $query = $this->database->query($sql);
        return $query->exec();
    }

    public function query(String $query): bool
    {
        $query = $this->database->query($query);
        return $query->exec();
    }

    public function delete(array $data): bool
    {
        $this->dataList = $this->parseData($data);
        $sql = $this->parseSQL(self::DELETE);
        $query = $this->database->query($sql);
        return $query->exec();
    }

    public function count(): int
    {
        $sql = self::parseSQL(self::SELECT);
        $query = $this->database->query($sql);
        return $query->rowCount();
    }
}
