<?php

namespace Alchemy\expression;
use Exception;

class InclusiveExpression extends Expression {
    protected $left;
    protected $in = array();


    public function __construct(Column $left, array $in) {
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
