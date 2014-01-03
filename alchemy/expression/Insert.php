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
    public function into(Table $table) {
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


    /**
     * @see Query::getParamters()
     */
    public function getParameters() {
        $params = array();
        foreach ($this->rows as $row) {
            foreach ($row as $value) {
                $params[] = $value;
            }
        }

        return $params;
    }
}
