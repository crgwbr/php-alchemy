<?php

namespace Alchemy\dialect;
use Alchemy\expression as expr;

class SQLiteCompiler extends ANSICompiler {

    public function Create(expr\Create $obj) {
        $table = $obj->getTable();

        $columns = $this->map('Create_Column', $table->listColumns());
        $queries = array();

        foreach ($table->listIndexes() as $name => $index) {
            $sql = $this->Create_Key($index);
            if ($index instanceof expr\PrimaryKey ||
                $index instanceof expr\ForeignKey) {
                $columns[] = $sql;
            } else {
                $queries[] = $sql;
            }
        }

        $columns = implode(', ', $columns);
        array_unshift($queries,
            "CREATE TABLE IF NOT EXISTS {$table->getName()} ({$columns})");

        return $queries;
    }


    public function Create_Index($obj) {
        $columns = $this->compile($obj->listColumns());
        $columns = implode(', ', $columns);

        $table = $obj->getTable()->getName();

        return "CREATE INDEX {$obj->getName()} ON {$table} ({$columns})";
    }


    public function Create_Integer(expr\Integer $obj) {
        return "INTEGER";
    }


    public function Create_UniqueKey($obj) {
        $columns = $this->compile($obj->listColumns());
        $columns = implode(', ', $columns);

        $table = $obj->getTable()->getName();

        return "CREATE UNIQUE INDEX {$obj->getName()} ON {$table} ({$columns})";
    }


    public function Update(expr\Update $obj) {
        $this->pushConfig(array('alias_tables' => false));
        $sql = parent::Update($obj);
        $this->popConfig();

        return $sql;
    }
}