<?php

namespace Alchemy\core\query;
use Alchemy\core\Element;


/**
 * Represent a JOIN clause
 */
class Join extends Element implements IQueryFragment {
    const LEFT = 'LEFT';
    const RIGHT = 'RIGHT';
    const FULL = 'FULL';
    const INNER = 'INNER';
    const OUTER = 'OUTER';

    protected $direction;
    protected $type;
    protected $table;
    protected $on;


    /**
     * Object constructor
     *
     * @param string $direction Join::LEFT or Join::RIGHT
     * @param string $type Join::FULL, Join::INNER, or Join::OUTER
     * @param Table $table
     * @param Expression $on
     */
    public function __construct($direction, $type, TableRef $table, Expression $on) {
        $this->direction = $direction;
        $this->type = $type;
        $this->table = &$table;
        $this->on = &$on;
        $this->addTag("sql.compile", "Join");
    }


    /**
     * @return string
     */
    public function getDirection() {
        return $this->direction;
    }


    /**
     * @return Expression
     */
    public function getOn() {
        return $this->on;
    }


    /**
     * Recursively get all scalar parameters used by this clause
     * in the order which they are used in the expression
     *
     * @return array(Scalar, Scalar, ...)
     */
    public function parameters() {
        return $this->on->parameters();
    }


    /**
     * @return Table
     */
    public function getTable() {
        return $this->table;
    }


    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }
}
