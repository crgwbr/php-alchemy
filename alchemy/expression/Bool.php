<?php

namespace Alchemy\expression;


class Bool extends Column {
    protected static $default_args = array();
    protected static $default_kwargs = array();


    public function decode($value) {
        return (bool)$value;
    }


    public function encode($value) {
        return new Scalar((bool)$value, Scalar::T_BOOL);
    }
}
