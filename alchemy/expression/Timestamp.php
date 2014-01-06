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
     * Decode a value from the RDBMS into a PHP value
     *
     * @param mixed $value
     * @return Datetime
     */
    public function decode($value) {
        return new Datetime($value);
    }


    /**
     * Encode a PHP value into something usable for the RDBMS.
     *
     * @param mixed $value
     * @return Scalar
     */
    public function encode($value) {
        if (!($value instanceof Datetime)) {
            $value = $this->decode($value);
        }

        $value = $value->format('Y-m-d H:i:s');
        return new Scalar($value, Scalar::T_STR);
    }
}
