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
     * @param IQueryValue $left
     * @param array $in
     */
    public function __construct(IQueryValue $left, array $in) {
        $this->left = &$left;

        foreach ($in as &$value) {
            if (!($value instanceof IQueryValue)) {
                throw new Exception("InclusiveExpression arguments must implement IQueryValue");
            }

            if ($value instanceof Scalar) {
                $this->scalars[] = &$value;
            }

            $this->in[] = &$value;
        }
    }
}
