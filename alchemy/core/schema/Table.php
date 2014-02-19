<?php

namespace Alchemy\core\schema;
use Alchemy\core\Element;
use Alchemy\core\query\TableRef;
use Alchemy\util\DataTypeLexer;
use Alchemy\util\promise\IPromisable;
use Exception;


/**
 * Represent a table in SQL
 */
class Table extends Element implements IPromisable {
    protected static $registered = array();

    protected $name;
    protected $resolved;
    protected $args = array();
    protected $columns = array();
    protected $indexes = array();

    private $dependancies = array();
    private $dependants = array();


    public static function list_promisable_methods() {
        return array(
            'getColumn' => "Alchemy\core\schema\Column",
            'getRef'    => "Alchemy\core\query\TableRef",
            'copy'      => "Alchemy\core\schema\Table");
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
     * @param string $name       name of table
     * @param array  $columndefs array("name" => Column, "name" => Column, ...)
     * @param array  $indexdefs  array("name" => Index, "name" => Index, ...)
     */
    public function __construct($type, $name, $args = array()) {
        parent::__construct($type);

        $this->name = $name;
        $def = static::get_definition($this->type);
        $this->args = self::normalize_arg($args, $def['defaults']);
    }


    public function getColumn($name) {
        if (!array_key_exists($name, $this->columns)) {
            if (array_key_exists($name, $this->args['columns'])) {
                $column = $this->args['columns'][$name];

                if (is_string($column)) {
                    $type = new DataTypeLexer($column);
                    $t = $type->getType();
                    $column = Column::$t($type->getArgs());
                }

                $this->columns[$name] = $column->copy(array(), $this, $name);
            } else {
                throw new Exception("Unknown column '{$this->name}.{$name}'");
            }
        }

        return $this->columns[$name];
    }


    public function copy() {
        return new static($this->name, $this->args);
    }


    /**
     * Get the table name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }


    public function getRef() {
        return new TableRef($this);
    }


    /**
     * Return true if the given column exists
     *
     * @param string $name
     * @return bool
     */
    public function isColumn($name) {
        return array_key_exists($name, $this->args['columns']);
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
     * Get this table's primary key index
     *
     * @return Index::PrimaryKey
     */
    public function getPrimaryKey() {
        $this->resolve();
        return array_key_exists('PRIMARY', $this->indexes)
            ? $this->indexes['PRIMARY']
            : null;
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
     * Lazy-resolve the whole Table
     */
    protected function resolve() {
        if ($this->resolved) return;
        $primary = array();

        foreach ($this->args['columns'] as $name => $prop) {
            $column = $this->getColumn($name);

            $this->indexes[$name] = $column->getIndex();

            if ($fk = $column->getForeignKey()) {
                $this->indexes[$fk->getName()] = $fk;

                $source = $fk->getSourceTable();
                if ($source != $this) {
                    $this->dependancies[] = $source->getName();
                    $source->dependants[] = $this->getName();
                }
            }

            if ($column->isPrimaryKeyPart()) {
                $primary[] = $column;
            }
        }

        if ($primary) {
            $this->indexes['PRIMARY'] = Index::PrimaryKey(array($primary), $this, 'PRIMARY');
        }

        // Set multi-column indexes
        foreach ($this->args['indexes'] as $name => $index) {
            if (is_string($index)) {
                $type = new DataTypeLexer($index);
                $t = $type->getType();
                $index = Index::$t($type->getArgs(), $this, $name);
            }

            $this->indexes[$name] = $index;
        }

        $this->indexes = array_filter($this->indexes);
        $this->resolved = true;
    }
}
