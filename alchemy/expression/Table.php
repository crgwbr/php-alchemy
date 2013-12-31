<?php

namespace Alchemy\expression;
use Alchemy\util\DataTypeLexer;
use Exception;


class Table {
    protected static $tableCounter = 0;

    protected $name;
    protected $alias;
    protected $columns = array();


    public function __construct($tableName, $columns, $namespace = "Alchemy\\expression") {
        $this->name = $tableName;
        $this->alias = strtolower(substr($tableName, 0, 2)) . (++static::$tableCounter);

        foreach ($columns as $columnName => $definition) {
            $type = new DataTypeLexer($definition);
            $columnClass = $namespace . '\\' . $type->getType();

            $args = $type->getArgs();
            $kwargs = $type->getKeywordArgs();

            $column = new $columnClass($this->alias, $columnName, $columnName, $args, $kwargs);
            $this->columns[$columnName] = $column;
        }
    }


    public function __get($name) {
        if (!array_key_exists($name, $this->columns)) {
            throw new Exception("Column {$name} does not exist");
        }

        return $this->columns[$name];
    }


    public function getName() {
        return $this->name;
    }


    public function isColumn($name) {
        return array_key_exists($name, $this->columns);
    }


    public function listColumns() {
        return $this->columns;
    }
}
