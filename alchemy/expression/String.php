<?php

namespace Alchemy\expression;


class String extends Column {
    protected static $default_args = array(255);
    protected static $default_kwargs = array(
        'collation' => null,
    );


    public function decode($value) {
        return (string)$value;
    }


    public function encode($value) {
        return new Scalar((string)$value, Scalar::T_STR);
    }
}
