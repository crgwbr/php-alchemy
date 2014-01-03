<?php

namespace Alchemy\expression;


/**
 * Represent a VARCHAR column in SQL
 */
class String extends Column {
    protected static $default_args = array(255);
    protected static $default_kwargs = array(
        'collation' => null,
    );


    /**
     * @see Column::decode()
     */
    public function decode($value) {
        return (string)$value;
    }


    /**
     * @see Column::encode()
     */
    public function encode($value) {
        return new Scalar((string)$value, Scalar::T_STR);
    }
}
