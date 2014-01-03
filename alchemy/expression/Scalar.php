<?php

namespace Alchemy\expression;
use PDO;


/**
 * Represent a Scalar value in SQL
 */
class Scalar extends Value {
    const T_BOOL = PDO::PARAM_BOOL;
    const T_NULL = PDO::PARAM_NULL;
    const T_INT = PDO::PARAM_INT;
    const T_STR = PDO::PARAM_STR;

    protected $dataType;
    protected $value;


    /**
     * Object constructor
     *
     * @param mixed Primitive Value
     * @param mixed $dataType Optional. Type will be inferred if not provided
     */
    public function __construct($value, $dataType = null) {
        $this->value = $value;
        $this->dataType = $dataType ?: $this->inferDataType($value);
    }


    /**
     * Get the data type
     *
     * @param integer Scalar::T_BOOL, etc
     */
    public function getDataType() {
        return $this->dataType;
    }


    /**
     * Get the primitive value
     *
     * @param mixed
     */
    public function getValue() {
        return $this->value;
    }


    /**
     * Guess the data type for the given value
     *
     * @param mixed $value
     * @return integer
     */
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
