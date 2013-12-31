<?php

namespace Alchemy\expression;
use Exception;


abstract class Expression {
    protected static $conjoin_types = array('and', 'or');

    protected $scalars = array();


    public function __call($name, $args) {
        if (!in_array($name, self::$conjoin_types)) {
            throw new Exception("Bad method called");
        }

        $compound = new CompoundExpression($this);
        $compound->$name($args[0]);
        return $compound;
    }


    public function getParameters() {
        return $this->scalars;
    }
}
