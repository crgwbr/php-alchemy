<?php

namespace Alchemy\expression;
use BadMethodCallException;


/**
 * Represent a SQL compound expression
 */
class CompoundExpression extends Expression {

    /**
     * Object constructor
     *
     * @param Expression $expr Base child expression
     */
    public function __construct(Expression $expr) {
        $this->elements[] = $expr;
    }


    /**
     * Add to the expression using either AND or OR conjoiners
     *
     * @param string $name "and" or "or"
     * @param array $args array([0] => Expression)
     */
    public function __call($name, $args) {
        if (!in_array($name, self::$conjoin_types)) {
            throw new Exception("Bad method called");
        }

        $this->elements[] = Operator::$name();
        $this->elements[] = $args[0];
        return $this;
    }


    /**
     * Recursively get all scalar parameters used by this expression
     *
     * @return array array(Scalar, Scalar, ...)
     */
    public function getParameters() {
        $params = parent::getParameters();

        foreach ($this->elements as $expr) {
            if (is_callable(array($expr, 'getParameters'))) {
                $params = array_merge($params, $expr->getParameters());
            }
        }

        return $params;
    }
}
