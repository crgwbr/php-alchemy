<?php

namespace Alchemy\expression;
use PDO;


class Scalar extends Value {
    const T_BOOL = PDO::PARAM_BOOL;
    const T_NULL = PDO::PARAM_NULL;
    const T_INT = PDO::PARAM_INT;
    const T_STR = PDO::PARAM_STR;

    protected $dataType;
    protected $value;


    public function __construct($value, $dataType = null) {
        $this->value = $value;
        $this->dataType = $dataType ?: $this->inferDataType($value);
    }


    public function getDataType() {
        return $this->dataType;
    }


    public function getValue() {
        return $this->value;
    }


    protected function inferDataType($value) {
        if (is_bool($value)) {
            return self::T_BOOL;
        }

        if (is_null($value)) {
            return self::T_NULL;
        }

        if (is_numeric($value)) {
            return self::T_INT;
        }

        return self::T_STR;
    }
}
