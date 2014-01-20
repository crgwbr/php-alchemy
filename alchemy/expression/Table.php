<?php

namespace Alchemy\expression;
use Alchemy\util\DataTypeLexer;
use Alchemy\util\promise\IPromisable;
use Exception;


/**
 * Represent a table in SQL
 */
class Table extends QueryElement implements IPromisable {
    protected static $registered = array();

    protected $name;

    private $indexdefs  = array();
    private $propdefs   = array();

    private $columns    = array();
    private $indexes    = array();
    private $properties = array();


    public static function list_promisable_methods() {
        $NS = __NAMESPACE__;
        return array(
            '__get' => "$NS\Column",
            'copy'  => "$NS\Table");
    }


    /**
     * Retrieve the table registered for a given name.
     *
     * @param  string $name
     * @return Table
     */
    public static function find($name) {
        if (isset(self::$registered[$name])) {
            return self::$registered[$name];
        }

        throw new \Exception("No table registered for name '{$table}'.");
    }


    /**
     * Object constructor
     *
     * @param string $tableName
     * @param array $columns array("name" => Column, "name" => Column, ...)
     * @param string $namespace Namespace of Column classes
     */
    public function __construct($name, $propdefs, $indexdefs = array(), $namespace = "Alchemy\\expression") {
        $this->name = $name;
        $this->propdefs  = $propdefs;
        $this->indexdefs = $indexdefs;
    }


    /**
     * Get a column instance by name
     *
     * @param string $name Column Name
     */
    public function __get($name) {
        if (!array_key_exists($name, $this->properties)) {
            $this->resolveProperty($name);
        }

        return $this->properties[$name];
    }


    public function copy() {
        return new static($this->name, $this->propdefs, $this->indexdefs);
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
        return array_key_exists($name, $this->propdefs)
            && ($this->{$name} instanceof Column);
    }


    /**
     * List all configured columns
     *
     * @return array array(Name => Column, ...)
     */
    public function listColumns() {
        $this->resolveTable();
        return $this->columns;
    }


    /**
     * List all additional column indexes
     *
     * @return array array(Name => Index, ...)
     */
    public function listIndexes() {
        $this->resolveTable();
        return $this->indexes;
    }


    /**
     * List the columns which make up this table's primary key
     *
     * @return array array(Name => Column)
     */
    public function listPrimaryKeyComponents() {
        foreach ($this->listIndexes() as $name => $index) {
            if ($index instanceof Primary) {
                return $index->listColumns();
            }
        }
    }


    /**
     * Register this Table as canonical for its name
     */
    public function register() {
        if (!isset(self::$registered[$this->name])) {
            self::$registered[$this->name] = $this;
        }

        throw new \Exception("A table is already registered for name '{$this->name}'.");
    }


    /**
     * Lazy-resolve a named property of this Table
     *
     * @param string $name name of property
     */
    protected function resolveProperty($name, $namespace="Alchemy\\expression") {
        if (array_key_exists($name, $this->propdefs)) {
            $column = $this->propdefs[$name];

            if (is_string($column)) {
                $type = new DataTypeLexer($column);
                $class = $namespace . '\\' . $type->getType();
                $column = new $class($type->getArgs());
            }

            $this->properties[$name] = $column->copy($this, $name);
        } else {
            throw new Exception("Column or relationship {$name} does not exist");
        }
    }


    /**
     * Lazy-resolve the whole Table
     */
    protected function resolveTable($namespace="Alchemy\\expression") {
        if ($this->columns) return;

        foreach ($this->propdefs as $name => $prop) {
            $column = $this->{$name};
            if (!($column instanceof Column)) {
                continue;
            }

            $this->columns[$name] = $column;
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
        foreach ($this->indexdefs as $name => $index) {
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
