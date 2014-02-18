<?php

namespace Alchemy\core\query;
use Alchemy\core\Element;


/**
 * Represents a DDL transformation query
 */
class DDLQuery extends Element implements IQuery {

    protected $table;

    /**
     * Object constructor
     *
     * @param Table $table
     */
    public function __construct($type, $table) {
        parent::__construct($type);
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
    public function parameters() {
        return array();
    }
}
