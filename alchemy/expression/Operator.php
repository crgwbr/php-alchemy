<?php

namespace Alchemy\expression;
use BadMethodCallException;


class Operator {
    const O_EQUAL = "=";
    const O_NOT_EQUAL = "!=";
    const O_GT = ">";
    const O_LT = "<";
    const O_GTE = ">=";
    const O_LTE = "<=";
    const O_LIKE = "LIKE";
    const O_AND = "AND";
    const O_OR = "OR";

    protected $type;

    public static function __callStatic($name, $args) {
        $const = "self::O_" . strtoupper($name);
        if (!defined($const)) {
            throw new BadMethodCallException("Bad Operator Type[{$name}]");
        }

        $oper = constant($const);
        return new self($oper);
    }

    public function __construct($type) {
        $this->type = $type;
    }

    public function __toString() {
        return $this->type;
    }
}
