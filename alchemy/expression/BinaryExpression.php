<?php

namespace Alchemy\expression;


/**
 * Represent a binary SQL expression
 */
class BinaryExpression extends Expression {
    protected $left;
    protected $right;
    protected $operator;


    /**
     * Object Constructor.
     *
     * @param IQueryValue $left
     * @param Operator $operator
     * @param IQueryValue $right
     */
    public function __construct(IQueryValue $left, Operator $operator, IQueryValue $right) {
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
}
