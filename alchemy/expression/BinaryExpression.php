<?php

namespace Alchemy\expression;


/**
 * Represent a binary SQL expression
 */
class BinaryExpression extends Expression {

    /**
     * Object Constructor.
     *
     * @param IQueryValue $left
     * @param Operator $operator
     * @param IQueryValue $right
     */
    public function __construct(IQueryValue $left, Operator $operator, IQueryValue $right) {
        $this->elements = array(&$left, &$operator, &$right);

        if ($left instanceof Scalar) {
            $this->scalars[] = &$left;
        }

        if ($right instanceof Scalar) {
            $this->scalars[] = &$right;
        }
    }
}
