<?php

namespace Alchemy\expression;


abstract class Scalar extends Value {
    protected static $data_type;
    protected $value;

    public function __construct($value) {
        $this->value = $value;
    }


    public function __toString() {
        return '?';
    }


    public function getDataType() {
        return static::$data_type;
    }


    public function getValue() {
        return $this->value;
    }
}
