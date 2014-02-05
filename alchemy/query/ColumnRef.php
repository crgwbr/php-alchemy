<?php

namespace Alchemy\query;
use Alchemy\util\promise\IPromisable;
use Alchemy\expression as expr;


/**
 * Represents a reference to a column in SQL query
 */
class ColumnRef extends expr\Element implements expr\IQueryValue, IPromisable {

    protected $schema;
    protected $table;


    public static function list_promisable_methods() {
        return array(
            'copy'   => "Alchemy\query\ColumnRef",
            'schema' => "Alchemy\expression\Column",
            'table'  => "Alchemy\query\TableRef");
    }


    /**
     * Build and return a Predicate by comparing this
     * column to another IQueryValue
     *
     * @param $name Operator Name: and, or
     * @param $args array([0] => IQueryValue, ...) IQueryValue to compare to
     */
    public function __call($name, $args) {
        foreach ($args as &$value) {
            if (!($value instanceof expr\IQueryValue)) {
                $value = new expr\Scalar($value);
            }
        }

        array_unshift($args, $this);
        return expr\Predicate::$name($args);
    }


    public function __construct($schema, TableRef $table) {
        $this->schema = $schema;
        $this->table = $table;

        $this->addTag('expr.value');
        $this->addTag('sql.compile', "ColumnRef");
    }


    public function name() {
        return $this->schema->getName();
    }


    public function schema() {
        return $this->schema;
    }


    public function table() {
        return $this->table;
    }
}