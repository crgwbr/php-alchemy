<?php

namespace Alchemy\core\query;
use Alchemy\core\Element;
use Alchemy\util\promise\IPromisable;


/**
 * Represents a reference to a table in a SQL query
 */
class TableRef extends Element implements IPromisable {

    protected $schema;


    public static function list_promisable_methods() {
        return array(
            '__get'  => "Alchemy\core\query\ColumnRef",
            'copy'   => "Alchemy\core\query\TableRef",
            'schema' => "Alchemy\core\schema\Table");
    }


    /**
     * Get a column reference by name
     *
     * @param  string    $name Column name
     * @return ColumnRef       reference to Column
     */
    public function __get($name) {
        $column = $this->schema->getColumn($name);
        return new ColumnRef($column, $this);
    }


    public function __construct($schema) {
        $this->schema = $schema;

        $this->addTag('sql.compile', "TableRef");
    }


    public function columns() {
        $columns = array();
        foreach($this->schema->listColumns() as $column) {
            $columns[] = new ColumnRef($column, $this);
        }

        return $columns;
    }


    /**
     * Returns a Predicate for filtering rows of this table
     * equal to the values of a map
     *
     * @param  array     $columns array('ColumnName' => Value)
     * @return Predicate
     */
    public function equal(array $columns) {
        $list = array();
        foreach($columns as $name => $value) {
            $list[] = $this->{$name}->equal($value);
        }

        return count($list) > 1
            ? Predicate::AND_($list)
            : ($list ? $list[0] : null);
    }


    public function name() {
        return $this->schema->getName();
    }


    public function schema() {
        return $this->schema;
    }
}