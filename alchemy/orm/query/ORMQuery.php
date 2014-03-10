<?php

namespace Alchemy\orm;
use Alchemy\core\query\Expression;
use Alchemy\core\query\Predicate;
use Alchemy\core\query\Query;
use Alchemy\core\query\TableRef;


/**
 * Represent an object-relationship-aware SQL query
 */
class ORMQuery extends Query {

    protected $joinedTables = array();
    protected $with = array();

    public function __construct($type, TableRef $table) {
        parent::__construct($type, $table);

        $this->joinedTables[$table->getID()] = $table;
        if ($table instanceof ORMTableRef && ($this->where = $table->predicate())) {
            $this->joins($this->where->tables());
        }

        $this->columns($table->columns());
    }


    public function __set($name, $value) {
        if ($value instanceof Expression) {
            $this->joins($value->tables());
        }

        parent::__set($name, $value);
    }


    public function join($table, Predicate $on = null, $direction = null, $type = null) {
        if (array_key_exists($table->getID(), $this->joinedTables)) {
            return $this;
        }

        $this->joinedTables[$table->getID()] = $table;
        if ($on) {
            $this->joins($on->tables());
        }

        return parent::join($table, $on, $direction, $type);
    }


    public function joins($args = false) {
        if ($args === false) {
            return $this->joins;
        }

        foreach ($args as $table) {
            $on = $table instanceof ORMTableRef ? $table->predicate() : null;
            $this->join($table, $on);
        }
    }


    public function where($expr = false) {
        if ($expr === false) {
            return $this->where;
        }

        $this->where = Predicate::ALL($this->where, func_get_args());
        $this->joins($this->where->tables());

        return $this;
    }
}