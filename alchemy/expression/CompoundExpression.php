<?php

namespace Alchemy\expression;
use BadMethodCallException;


class CompoundExpression extends Expression {
    protected $components = array();


    public function __construct(Expression $expr) {
        $this->components[] = $expr;
    }


    public function __call($name, $args) {
        if (!in_array($name, self::$conjoin_types)) {
            throw new Exception("Bad method called");
        }

        $this->components[] = Operator::$name();
        $this->components[] = $args[0];
        return $this;
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
