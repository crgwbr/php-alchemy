<?php

namespace Alchemy\expression;


abstract class Expression {
    protected $scalars = array();

    public function listScalars() {
        return $this->scalars;
    }
}
