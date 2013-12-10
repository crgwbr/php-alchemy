<?php

namespace Alchemy\expression;
use BadMethodCallException;


class CompoundExpression extends Expression {
    protected static $conjoinTypes = array('and', 'or');

    protected $components = array();


    public function __construct(Expression $expr) {
        $this->components[] = $expr;
    }


    public function __call($conjoin, $args) {
        if (!in_array($conjoin, static::$conjoinTypes)) {
            throw new BadMethodCallException("Bad Expression Conjoiner[{$conjoin}]");
        }

        $expr = array_pop($args);
        if (!$expr instanceof Expression) {
            throw new Exception("Invalid Expression Supplied");
        }

        $this->components[] = Operator::$conjoin();
        $this->components[] = &$expr;
    }


    public function __toString() {
        $expr = implode(" ", $this->components);
        return "({$expr})";
    }


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
