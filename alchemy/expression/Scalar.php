<?php

namespace Alchemy\expression;
use PDO;


/**
 * Represent a Scalar value in SQL
 */
class Scalar extends QueryElement implements IQueryValue {
    const T_BOOL = PDO::PARAM_BOOL;
    const T_NULL = PDO::PARAM_NULL;
    const T_INT = PDO::PARAM_INT;
    const T_STR = PDO::PARAM_STR;

    protected static $scalarCounter = 0;

    protected $dataType;
    protected $value;
    protected $id;


    /**
     * Object constructor
     *
     * @param mixed Primitive Value
     * @param mixed $dataType Optional. Type will be inferred if not provided
     */
    public function __construct($value, $dataType = null) {
        $this->id = self::$scalarCounter++;
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
     * Return the id of this parameter
     *
     * @return string
     */
    public function getID() {
        return $this->id;
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
