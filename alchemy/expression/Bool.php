<?php

namespace Alchemy\expression;


/**
 * Represent a Boolean Column
 */
class Bool extends Column {

    /**
     * @see Column::decode()
     */
    public function decode($value) {
        return (bool)$value;
    }


    /**
     * @see Column::encode()
     */
    public function encode($value) {
        return new Scalar((bool)$value, Scalar::T_BOOL);
    }
}
