<?php

namespace Alchemy\core\query;
use Alchemy\core\Element;


/**
 * Abstract class for representing a DDL transformation query
 */
abstract class DDLQuery extends Element implements IQuery {
    protected $table;


    /**
     * Object constructor
     *
     * @param Table $table
     */
    public function __construct($table) {
        $this->table = $table;

        $parts = explode('\\', get_called_class());
        $cls = array_pop($parts);
        $this->addTag("sql.compile", $cls);
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
