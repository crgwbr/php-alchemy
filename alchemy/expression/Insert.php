<?php

namespace Alchemy\expression;
use Exception;


/**
 * Represent an INSERT statement
 */
class Insert extends Query {
    protected $into;
    protected $rows = array();


    /**
     * Set the table to insert into
     *
     * @param Table $table
     */
    public function into(Table $table = null) {
        if (is_null($table)) {
            return $this->into;
        }

        $this->into = $table;
    }


    /**
     * Add a row to insert. Each parameter corresponds to
     * a column set with {@link Query::columns()}. Optionally
     * Send all columns as a single array.
     */
    public function row() {
        $columns = func_get_args();
        $columns = is_array($columns[0]) ? $columns[0] : $columns;
        $row = array();
        foreach ($columns as $column) {
            if (!$column instanceof Scalar) {
                $column = new Scalar($column);
            }

            $row[] = $column;
        }

        $this->rows[] = $row;
    }


    public function rows() {
        return $this->rows;
    }


    /**
     * Recursively get all scalar parameters used by this expression
     *
     * @return array array(Scalar, Scalar, ...)
     */
    public function getParameters() {
        $params = parent::getParameters();
        foreach ($this->rows as $row) {
            foreach ($row as $value) {
                $params[] = $value;
            }
        }

        return $params;
    }
}
