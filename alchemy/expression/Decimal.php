<?php

namespace Alchemy\expression;


/**
 * Represent an Decimal in SQL
 */
class Decimal extends Column {
    protected static $default_args = array(5, 2);


    /**
     * Decode a value from the RDBMS into a PHP value
     *
     * @param mixed $value
     * @return integer
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


    /**
     * Get the decimal precision
     *
     * @return integer
     */
    public function getPrecision() {
        return $this->args[0];
    }


    /**
     * Get the decimal scale
     *
     * @return integer
     */
    public function getScale() {
        return $this->args[1];
    }
}
