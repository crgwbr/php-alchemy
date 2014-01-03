<?php

namespace Alchemy\util;


/**
 * Allow method chaining and simulated object immutablity
 * by performing every method call on a clone of the object
 * you started with.
 */
class Monad {
    protected $value;


    /**
     * Object constructor.
     *
     * @param Object $value Object to wrap
     */
    public function __construct($value) {
        $this->value = $value;
    }


    /**
     * Clone the inner object, call a method on it, and return it
     *
     * @param string $fn Method Name
     * @param array $args
     * @return Monad
     */
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


    /**
     * Force PHP to deep clone
     */
    public function __clone() {
        $this->value = clone $this->value;
    }


    /**
     * Unwrap the value
     *
     * @return mixed
     */
    public function unwrap() {
        return clone $this->value;
    }
}
