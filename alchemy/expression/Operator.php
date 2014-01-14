<?php

namespace Alchemy\expression;
use BadMethodCallException;


/**
 * Represent a logical operator
 */
class Operator {
    const O_EQUAL = "=";
    const O_NOT = "!=";
    const O_GT = ">";
    const O_LT = "<";
    const O_GTE = ">=";
    const O_LTE = "<=";
    const O_LIKE = "LIKE";
    const O_AND = "AND";
    const O_OR = "OR";

    protected $type;


    /**
     * Construct an operator of the given type
     *
     * @param $name Operator name constant (Operator::O_*)
     * @return Operator
     */
    public static function __callStatic($name, $args) {
        $const = "static::O_" . strtoupper($name);
        if (!defined($const)) {
            throw new BadMethodCallException("Bad Operator Type[{$name}]");
        }

        $oper = constant($const);
        return new static($oper);
    }


    public function __construct($type) {
        $this->type = $type;
    }


    public function getType() {
        return $this->type;
    }
}
