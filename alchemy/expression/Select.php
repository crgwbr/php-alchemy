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
    public function from($table = null) {
        if (is_null($table)) {
            return $this->from;
        }

        $this->from = $table;
    }
}
