<?php

namespace Alchemy\dialect;
use Exception;


class SQLite_Create extends ANSI_DialectBase {

    public function __toString() {
        $table = $this->table->getName();

        $columns = array();
        foreach ($this->table->columns as $column) {
            $columns[] = $column->definition();
        }

        $columns = implode(", ", $columns);
        $sql = "CREATE TABLE IF NOT EXISTS {$table} ({$columns})";
        return $sql;
    }
}


class SQLite_Integer extends ANSI_Integer {
    public function definition() {
        $sql = "{$this->name} INTEGER ";
        $sql .= $this->getKwarg('null') ? "NULL" : "NOT NULL";

        if ($this->getKwarg('primary_key')) {
            $sql .= " PRIMARY KEY";

            if ($this->getKwarg('auto_increment')) {
                $sql .= " AUTOINCREMENT";
            }
        }

        return $sql;
    }
}
