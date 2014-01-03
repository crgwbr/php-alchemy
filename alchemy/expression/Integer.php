<?php

namespace Alchemy\expression;


/**
 * Represent an Integer in SQL
 */
class Integer extends Column {
    protected static $default_args = array(11);
    protected static $default_kwargs = array(
        'auto_increment' => false,
        'unsigned' => false,
    );


    /**
     * @see Column::decode()
     */
    public function decode($value) {
        return (int)$value;
    }


    /**
     * @see Column::encode()
     */
    public function encode($value) {
        return new Scalar((int)$value, Scalar::T_INT);
    }
}
