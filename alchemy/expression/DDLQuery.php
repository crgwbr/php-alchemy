<?php

namespace Alchemy\expression;


/**
 * Abstract class for representing a DDL transformation query
 */
abstract class DDLQuery implements IQuery {
    protected $table;


    /**
     * Object constructor
     *
     * @param Table $table
     */
    public function __construct(Table $table) {
        $this->table = $table;
    }


    /**
     * @return Table
     */
    public function getTable() {
        return $this->table;
    }


    /**
     * Recursively get all scalar parameters used by this expression
     *
     * @return array array(Scalar, Scalar, ...)
     */
    public function getParameters() {
        return array();
    }
}
