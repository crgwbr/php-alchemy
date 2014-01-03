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
