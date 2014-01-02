<?php

namespace Alchemy\expression;


class Bool extends Column {
    public function decode($value) {
        return (bool)$value;
    }


    public function encode($value) {
        return new Scalar((bool)$value, Scalar::T_BOOL);
    }
}
