<?php

namespace Alchemy\expression;
use Alchemy\util\DataTypeLexer;
use Alchemy\util\promise\IPromisable;
use Exception;


/**
 * Represent a table in SQL
 */
class Table extends Element implements IPromisable {
    protected static $registered = array();

    protected $name;

    private $indexdefs  = array();
    private $propdefs   = array();

    private $columns    = array();
    private $indexes    = array();
    private $properties = array();
    private $dependancies = array();
    private $dependants = array();


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

        throw new \Exception("No table registered for name '{$name}'.");
    }


    /**
     * Object constructor
     *
     * @param string $tableName
     * @param array $columns array("name" => Column, "name" => Column, ...)
     * @param string $namespace Namespace of Column classes
     */
    public function __construct($name, $propdefs, $indexdefs = array()) {
        $this->name = $name;
        $this->propdefs  = $propdefs;
        $this->indexdefs = $indexdefs;
        $this->addTag('sql.compile', 'Table');
        $this->addTag('sql.create', 'Table');
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
        $this->resolve();
        return $this->columns;
    }


    /**
     * List names of table I depend on
     *
     * @return array
     */
    public function listDependancies() {
        $this->resolve();
        return $this->dependancies;
    }


    /**
     * List names of tables that depend on me
     *
     * @return array
     */
    public function listDependants() {
        $this->resolve();
        return $this->dependants;
    }


    /**
     * List all additional column indexes
     *
     * @return array array(Name => Index, ...)
     */
    public function listIndexes() {
        $this->resolve();
        return $this->indexes;
    }


    /**
     * List the columns which make up this table's primary key
     *
     * @return array array(Name => Column)
     */
    public function listPrimaryKeyComponents() {
        foreach ($this->listIndexes() as $name => $index) {
            if ($index instanceof PrimaryKey) {
                return $index->listColumns();
            }
        }
    }


    /**
     * Register this Table as canonical for its name
     */
    public function register() {
        if (isset(self::$registered[$this->name])
            && self::$registered[$this->name] !== $this) {
            throw new \Exception("A table is already registered for name '{$this->name}'.");
        }

        self::$registered[$this->name] = $this;
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

            $this->properties[$name] = $column->copy(array(), $this, $name);
        } else {
            throw new Exception("Column or relationship {$name} does not exist");
        }
    }


    /**
     * Lazy-resolve the whole Table
     */
    protected function resolve($namespace="Alchemy\\expression") {
        if ($this->columns) return;
        $primary = array();

        foreach ($this->propdefs as $name => $prop) {
            $column = $this->{$name};
            if (!($column instanceof Column)) {
                continue;
            }

            $this->columns[$name] = $column;
            $this->indexes[$name] = $column->getIndex();

            $fk = $column->getForeignKey();
            if ($fk) {
                $this->indexes[] = $fk;

                $source = $fk->getSourceTable();
                $name = $source->getName();
                if ($name != $this->getName()) {
                    $this->dependancies[] = $name;
                    $source->dependants[] = $this->getName();
                }
            }

            if ($column->isPrimaryKeyPart()) {
                $primary[] = $column;
            }
        }

        if ($primary) {
            $this->indexes['PRIMARY'] = new PrimaryKey(array($primary), $this, 'PRIMARY');
        }

        // Set multi-column indexes
        foreach ($this->indexdefs as $name => $index) {
            if (is_string($index)) {
                $type = new DataTypeLexer($index);
                $class = $namespace . '\\' . $type->getType();
                $index = new $class($type->getArgs(), $this, $name);
            }

            $this->indexes[$name] = $index;
        }

        $this->indexes = array_filter($this->indexes);
    }
}
