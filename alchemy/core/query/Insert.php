<?php

namespace Alchemy\core\query;
use Exception;


/**
 * Represent an INSERT statement
 */
class Insert extends Query {
    protected $rows = array();


    /**
     * Add a row to insert. Each parameter corresponds to
     * a column set with {@see Query::columns()}. Optionally
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
        return $this;
    }


    /**
     * Get the rows to insert
     *
     * @return array
     */
    public function rows() {
        $default = array();
        foreach (array_values($this->columns()) as $index => $column) {
            if ($column instanceof Scalar) {
                $default[$index] = $column;
            } elseif ($column instanceof ColumnRef) {
                $default[$index] = Expression::NULL();
            }
        }

        $rows = array();
        foreach ($this->rows as $row) {
            $rows[] = $row + $default;
        }

        return $rows;
    }


    /**
     * Recursively get all scalar parameters used by this expression
     *
     * @return array array(Scalar, Scalar, ...)
     */
    public function parameters() {
        $params = parent::parameters();
        foreach ($this->rows as $row) {
            foreach ($row as $value) {
                $params[] = $value;
            }
        }

        return $params;
    }
}
