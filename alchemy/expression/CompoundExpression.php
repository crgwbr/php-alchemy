<?php

namespace Alchemy\expression;
use BadMethodCallException;


class CompoundExpression extends Expression {
    protected static $conjoinTypes = array('and', 'or');

    protected $components = array();

    public function __construct(Expression $expr) {
        $this->components[] = $expr;
    }

    public function __call($conjoin, Expression $expr) {
        if (!in_array($conjoin, self::$conjoinTypes)) {
            throw new BadMethodCallException("Bad Expression Conjoiner[{$conjoin}]");
        }

        $this->components[] = Operator::$conjoin();
        $this->components[] = &$expr;
    }

    public function __toString() {
        $expr = implode(" ", $this->components);
        return "({$expr})";
    }
}
