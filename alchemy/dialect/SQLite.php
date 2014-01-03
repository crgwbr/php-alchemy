<?php

namespace Alchemy\dialect;
use Exception;


/**
 * SQLite vernacular of CREATE statements
 */
class SQLite_Create extends ANSI_DialectBase {

    /**
     * String Cast
     */
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


/**
 * SQLite vernacular for Integer columns
 */
class SQLite_Integer extends ANSI_Integer {

    /**
     * @see ANSI_Integer:::definition()
     */
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
