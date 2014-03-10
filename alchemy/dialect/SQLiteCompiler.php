<?php

namespace Alchemy\dialect;

class SQLiteCompiler extends ANSICompiler {

    protected static $schema_formats = array(
        'Integer'    => "INTEGER",
        'Index'      => 'CREATE INDEX %s_%s ON %1$s (%3$//, /)',
        'UniqueKey'  => 'CREATE UNIQUE INDEX %s_%s ON %1$s (%3$//, /)',
        'PrimaryKey' => "PRIMARY KEY (%3$//, /)",
        'Timestamp'  => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");


    public function Create($obj) {
        $table = $obj->getTable();

        $columns = $this->map('Create_Element', $table->listColumns());
        $queries = array();

        foreach ($table->listIndexes() as $name => $index) {
            $sql = $this->Create_Element($index);
            if ($index->getType() == 'PrimaryKey' ||
                $index->getType() == 'ForeignKey') {
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


    public function Update($obj) {
        $this->pushConfig(array('alias_tables' => false));
        $sql = parent::Update($obj);
        $this->popConfig();

        return $sql;
    }
}