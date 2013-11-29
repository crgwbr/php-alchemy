<?php

namespace Alchemy\expression;


abstract class Expression {
    protected $scalars = array();


    public function getParameters() {
        return $this->scalars;
    }
}
