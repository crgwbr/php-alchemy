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
 * Represent an DELETE statement
 */
class SQLite_Delete extends ANSI_Delete {

    /**
     * Get an array of dialect specific settings
     *
     * @return array
     */
    public static function settings() {
        $settings = parent::settings();
        $settings['USE_TABLE_ALIASES'] = false;
        return $settings;
    }
}



/**
 * SQLite vernacular for Integer columns
 */
class SQLite_Integer extends ANSI_Integer {

    /**
     * Column Definition for a create table statement
     *
     * @return string
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



/**
 * Represent an UPDATE statement
 */
class SQLite_Update extends ANSI_Update {

    /**
     * Get an array of dialect specific settings
     *
     * @return array
     */
    public static function settings() {
        $settings = parent::settings();
        $settings['USE_TABLE_ALIASES'] = false;
        return $settings;
    }
}
