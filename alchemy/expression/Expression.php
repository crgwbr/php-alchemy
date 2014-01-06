<?php

namespace Alchemy\expression;
use Exception;


/**
 * Abstract base class for representing an expression in SQL
 */
abstract class Expression implements IQueryFragment {
    protected static $conjoin_types = array('and', 'or');

    protected $scalars = array();


    /**
     * Convert this expression into a CompoundExpression
     * using either an AND or OR conjoiner
     *
     * @param string $name "and" or "or"
     * @param array $args array([0] => Expression)
     */
    public function __call($name, $args) {
        if (!in_array($name, self::$conjoin_types)) {
            throw new Exception("Bad method called");
        }

        $compound = new CompoundExpression($this);
        $compound->$name($args[0]);
        return $compound;
    }


    /**
     * Recursively get all scalar parameters used by this expression
     *
     * @return array array(Scalar, Scalar, ...)
     */
    public function getParameters() {
        return $this->scalars;
    }
}
