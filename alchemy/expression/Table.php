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
    protected $indexes = array();


    /**
     * Object constructor
     *
     * @param string $tableName
     * @param array $columns array("name" => Column, "name" => Column, ...)
     * @param string $namespace Namespace of Column classes
     */
    public function __construct($tableName, $columns, $indexes = array(), $namespace = "Alchemy\\expression") {
        $this->name = $tableName;
        $this->id = ++static::$tableCounter;
        $this->setColumns($columns, $namespace);
        $this->setIndexes($indexes, $namespace);
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
        return $this->indexes;
    }


    /**
     * List the columns which make up this table's primary key
     *
     * @return array array(Name => Column)
     */
    public function listPrimaryKeyComponents() {
        foreach ($this->indexes as $name => $index) {
            if ($index instanceof Primary) {
                return $index->listColumns();
            }
        }
    }


    /**
     * Set columns for this Table
     *
     * @param array $columns Should be an associated array of name => column definitions
     */
    protected function setColumns($columns, $namespace) {
        $this->columns = array();

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
     * Set indexes for this Table
     *
     * @param array $indexes Should be an associated array of name => index definitions
     */
    protected function setIndexes($indexes, $namespace) {
        $this->indexes = array();

        // Set single column indexes
        foreach ($this->columns as $name => $column) {
            $type = null;

            if ($column->isPrimaryKey()) {
                $type = 'Primary';
            } elseif ($column->isUnique()) {
                $type = 'Unique';
            } elseif ($column->hasIndex()) {
                $type = 'Index';
            }

            if ($type) {
                $type = __NAMESPACE__ . "\\{$type}";
                $this->indexes[$name] = new $type($name, array($name => $column));
            }
        }

        // Set multi-column indexes
        foreach ($indexes as $name => $index) {
            if (is_string($index)) {
                $type = new DataTypeLexer($index);
                $class = $namespace . '\\' . $type->getType();

                $columns = array_fill_keys($type->getArgs(), true);
                $columns = array_intersect_key($this->columns, $columns);

                $index = new $class($name, $columns);
            }

            $this->indexes[$name] = $index;
        }
    }
}
