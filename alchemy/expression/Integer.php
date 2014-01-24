<?php

namespace Alchemy\expression;


/**
 * Represent an Integer in SQL
 */
class Integer extends Column {
    protected static $default_args = array(11,
        'auto_increment' => false,
        'unsigned' => false,
    );


    /**
     * Decode a value from the RDBMS into a PHP value
     *
     * @param mixed $value
     * @return integer
     */
    public function decode($value) {
        return (int)$value;
    }


    /**
     * Encode a PHP value into something usable for the RDBMS.
     *
     * @param mixed $value
     * @return Scalar
     */
    public function encode($value) {
        return new Scalar((int)$value, 'integer');
    }


    /**
     * Get the display width of the integer
     *
     * @return integer
     */
    public function getSize() {
        return $this->args[0];
    }


    /**
     * Is this columns auto incrementing?
     *
     * @return bool
     */
    public function isAutoIncremented() {
        return $this->args['auto_increment'];
    }
}
