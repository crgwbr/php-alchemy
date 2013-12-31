<?php

namespace Alchemy\expression;
use Datetime;


class Timestamp extends Column {
    protected static $default_args = array();
    protected static $default_kwargs = array();

    public function decode($value) {
        return new Datetime($value);
    }


    public function encode($value) {
        if (!($value instanceof Datetime)) {
            $value = $this->decode($value);
        }

        $value = $value->format('Y-m-d H:i:s');
        return new Scalar($value, Scalar::T_STR);
    }
}
