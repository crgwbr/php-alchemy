<?php

namespace Alchemy\expression;


/**
 * Represent a Blob column in SQL
 */
class Blob extends Column {
    protected static $default_args = array();


    /**
     * Decode a value from the RDBMS into a PHP value
     *
     * @param mixed $value
     * @return string
     */
    public function decode($value) {
        return (string)$value;
    }


    /**
     * Encode a PHP value into something usable for the RDBMS.
     *
     * @param mixed $value
     * @return Scalar
     */
    public function encode($value) {
        return new Scalar((string)$value, 'string');
    }
}
