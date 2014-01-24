<?php

namespace Alchemy\expression;


/**
 * Represent a Boolean Column
 */
class Bool extends Column {

    /**
     * Decode a value from the RDBMS into a PHP value
     *
     * @param mixed $value
     * @return bool
     */
    public function decode($value) {
        return (bool)$value;
    }


    /**
     * Encode a PHP value into something usable for the RDBMS.
     *
     * @param mixed $value
     * @return Scalar
     */
    public function encode($value) {
        return new Scalar((bool)$value, 'boolean');
    }
}
