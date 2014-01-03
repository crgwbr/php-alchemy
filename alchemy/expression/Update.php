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
    public function table(Table $table) {
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

        $this->values[$column] = $value;
    }


    /**
     * @see IQuery::getParameters
     */
    public function getParameters() {
        return $this->values;
    }
}
