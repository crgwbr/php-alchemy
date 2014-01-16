<?php

namespace Alchemy\expression;
use Exception;


/**
 * Represent an UPDATE statment in SQL
 */
class Update extends Query {
    protected $table;
    protected $values = array();


    /**
     * Set the table into update
     *
     * @param Table $table
     */
    public function table(Table $table = null) {
        if (is_null($table)) {
            return $this->table;
        }

        $this->table = $table;
    }


    /**
     * Set the given column to be equal to the given Value
     *
     * @param Column $column
     * @param mixed $value
     */
    public function set(Column $column, $value) {
        if (!$value instanceof Scalar) {
            $value = new Scalar($value);
        }

        $this->values[] = array($column, $value);
    }


    /**
     * Return an array of set operations to perform
     *
     * @return array
     */
    public function sets() {
        return $this->values;
    }


    /**
     * Recursively get all scalar parameters used by this expression
     *
     * @return array array(Scalar, Scalar, ...)
     */
    public function getParameters() {
        $scalars = parent::getParameters();
        foreach ($this->values as $value) {
            $scalars[] = $value[1];
        }
        return $scalars;
    }
}
