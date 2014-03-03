<?php

namespace Alchemy\core\query;
use Alchemy\core\Element;
use Alchemy\util\promise\IPromisable;


/**
 * Represents a reference to a column in SQL query
 */
class ColumnRef extends Element implements IQueryValue, IPromisable {

    protected $schema;
    protected $table;


    public static function list_promisable_methods() {
        return array(
            'copy'   => "Alchemy\core\query\ColumnRef",
            'schema' => "Alchemy\core\schema\Column",
            'table'  => "Alchemy\core\query\TableRef");
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
            if (!($value instanceof IQueryValue)) {
                $value = new Scalar($value);
            }
        }

        array_unshift($args, $this);
        return Predicate::$name($args);
    }


    public function __construct($schema, TableRef $table) {
        $this->schema = $schema;
        $this->table = $table;

        $this->addTag('expr.value');
        $this->addTag('sql.compile', "ColumnRef");
    }


    public function getDescription($maxdepth = 3, $curdepth = 0) {
        $str = parent::getDescription($maxdepth, $curdepth);
        return "$str ({$this->table->name()}#{$this->table->getID()}.{$this->name()})";
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