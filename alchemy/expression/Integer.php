<?php

namespace Alchemy\expression;


class Integer extends Column {
    protected static $default_args = array(11);
    protected static $default_kwargs = array(
        'auto_increment' => false,
        'unsigned' => false,
    );


    public function decode($value) {
        return (int)$value;
    }


    public function encode($value) {
        return new Scalar((int)$value, Scalar::T_INT);
    }
}
