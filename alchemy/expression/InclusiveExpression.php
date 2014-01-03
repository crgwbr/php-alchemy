<?php

namespace Alchemy\expression;
use Exception;


/**
 * Represent an inclusive expression in SQL
 */
class InclusiveExpression extends Expression {
    protected $left;
    protected $in = array();


    /**
     * Object constructor. Tests if $left is in $in
     *
     * @param Value $left
     * @param array $in
     */
    public function __construct(Value $left, array $in) {
        $this->left = &$left;

        foreach ($in as &$scalar) {
            if (!($scalar instanceof Scalar)) {
                throw new Exception("InclusiveExpression arguments must be instance of Scalar");
            }

            $this->in[] = &$scalar;
            $this->scalars[] = &$scalar;
        }
    }
}
