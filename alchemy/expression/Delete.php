<?php

namespace Alchemy\expression;
use Exception;


/**
 * Represent a DELETE in statement SQL
 */
class Delete extends Query {
    protected $from;


    /**
     * Set the table to delete from
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
