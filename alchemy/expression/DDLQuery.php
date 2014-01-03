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
     * @see IQuery::getParameters()
     */
    public function getParameters() {
        return array();
    }
}
