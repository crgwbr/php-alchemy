<?php

namespace Alchemy\expression;
use Alchemy\util\DataTypeLexer;
use Exception;


/**
 * Represent a table in SQL
 */
class Table {
    protected static $tableCounter = 0;

    protected $name;
    protected $alias;
    protected $columns = array();


    /**
     * Object constructor
     *
     * @param string $tableName
     * @param array $columns array("name" => Column, "name" => Column, ...)
     * @param string $namespace Namespace of Column classes
     */
    public function __construct($tableName, $columns, $namespace = "Alchemy\\expression") {
        $this->name = $tableName;
        $this->id = ++static::$tableCounter;

        foreach ($columns as $name => $column) {
            if (is_string($column)) {
               $type = new DataTypeLexer($column);
               $class = $namespace . '\\' . $type->getType();
               $column = new $class($type->getArgs());
            }

            $column->assign($this, $name, $name);
            $this->columns[$name] = $column;
        }
    }


    /**
     * Get a column instance by name
     *
     * @param string $name Column Name
     */
    public function __get($name) {
        if (!array_key_exists($name, $this->columns)) {
            throw new Exception("Column {$name} does not exist");
        }

        return $this->columns[$name];
    }


    /**
     * Get the table id
     *
     * @return string
     */
    public function getID() {
        return $this->id;
    }


    /**
     * Get the table name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }


    /**
     * Return true if the given column exists
     *
     * @param string $name
     * @return bool
     */
    public function isColumn($name) {
        return array_key_exists($name, $this->columns);
    }


    /**
     * List all configured columns
     *
     * @return array array(Name => Column, ...)
     */
    public function listColumns() {
        return $this->columns;
    }


    /**
     * List all additional column indexes
     *
     * @return array array(Index, ...)
     */
    public function listIndexes() {
        return array();
    }


    /**
     * List the columns which make up this table's primary key
     *
     * @return array array(Name => Column)
     */
    public function listPrimaryKeyComponents() {
        $pk = array();
        foreach ($this->columns as $name => $column) {
            if ($column->isPrimaryKey()) {
                $pk[$name] = $column;
            }
        }

        return $pk;
    }
}
