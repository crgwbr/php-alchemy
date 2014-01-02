<?php

namespace Alchemy\util;

class Monad {
    protected $value;


    public function __construct($value) {
        $this->value = $value;
    }


    public function __call($fn, $args) {
        $that = clone $this;

        $method = array($that->value, $fn);
        $value = call_user_func_array($method, $args);

        // Returned nothing, is probably just did internal mutation
        if (is_object($value)) {
            $that->value = $value;
            return $that;
        }

        // Returned a value to send to the user
        if (!is_null($value)) {
            return $value;
        }

        return $that;
    }


    public function unwrap() {
        return clone $this->value;
    }
}