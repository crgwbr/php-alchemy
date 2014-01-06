<?php

namespace Alchemy\expression;
use Exception;


/**
 * Represent a SELECT in statement SQL
 */
class Select extends Query {
    protected $from;


    /**
     * Set the table to select from
     *
     * @param Table $table
     */
    public function from(Table $table) {
        $this->from = $table;
    }


    /**
     * Recursively get all scalar parameters used by this expression
     *
     * @return array array(Scalar, Scalar, ...)
     */
    public function getParameters() {
        $params = array();

        foreach ($this->columns as $column) {
            if ($column instanceof Scalar) {
                $params[] = $column;
            }
        }

        foreach ($this->joins as $join) {
            $params = array_merge($params, $join->getParameters());
        }

        $params = $this->where
            ? array_merge($params, $this->where->getParameters())
            : $params;

        return $params;
    }
}
