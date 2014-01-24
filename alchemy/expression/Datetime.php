<?php

namespace Alchemy\expression;


/**
 * Represent a SQL Datetime column
 */
class Datetime extends Column {
    protected static $default_args = array();


    /**
     * Decode a value from the RDBMS into a PHP value
     *
     * @param mixed $value
     * @return Datetime
     */
    public function decode($value) {
        return new \Datetime($value);
    }


    /**
     * Encode a PHP value into something usable for the RDBMS.
     *
     * @param mixed $value
     * @return Scalar
     */
    public function encode($value) {
        if (!($value instanceof \Datetime)) {
            $value = $this->decode($value);
        }

        $value = $value->format('Y-m-d H:i:s');
        return new Scalar($value, 'string');
    }
}
