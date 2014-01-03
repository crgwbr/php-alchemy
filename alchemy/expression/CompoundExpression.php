<?php

namespace Alchemy\expression;
use BadMethodCallException;


/**
 * Represent a SQL compound expression
 */
class CompoundExpression extends Expression {
    protected $components = array();


    /**
     * Object constructor
     *
     * @param Expression $expr Base child expression
     */
    public function __construct(Expression $expr) {
        $this->components[] = $expr;
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

        $this->components[] = Operator::$name();
        $this->components[] = $args[0];
        return $this;
    }


    /**
     * @see Expression::getParameters()
     */
    public function getParameters() {
        $params = array();

        foreach ($this->components as $expr) {
            if (is_callable(array($expr, 'getParameters'))) {
                $params = array_merge($params, $expr->getParameters());
            }
        }

        return $params;
    }
}
