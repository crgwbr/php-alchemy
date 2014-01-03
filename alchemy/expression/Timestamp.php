<?php

namespace Alchemy\expression;
use Datetime;


/**
 * Represent a SQL Timestamp column
 */
class Timestamp extends Column {
    protected static $default_args = array();
    protected static $default_kwargs = array();


    /**
     * @see Column::decode()
     */
    public function decode($value) {
        return new Datetime($value);
    }


    /**
     * @see Column::encode()
     */
    public function encode($value) {
        if (!($value instanceof Datetime)) {
            $value = $this->decode($value);
        }

        $value = $value->format('Y-m-d H:i:s');
        return new Scalar($value, Scalar::T_STR);
    }
}
