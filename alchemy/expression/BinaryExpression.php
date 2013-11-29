<?php

namespace Alchemy\expression;


class BinaryExpression extends Expression {
    protected $left;
    protected $right;
    protected $operator;

    public function __construct(Value $left, Operator $operator, Value $right) {
        $this->left = &$left;
        $this->operator = &$operator;
        $this->right = &$right;

        if ($left instanceof Scalar) {
            $this->scalars[] = &$left;
        }

        if ($right instanceof Scalar) {
            $this->scalars[] = &$right;
        }
    }

    public function __toString() {
        return "{$this->left} {$this->operator} {$this->right}";
    }
}
