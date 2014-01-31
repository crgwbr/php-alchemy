<?php

namespace Alchemy\expression;

/**
 * Represents a true/false logical predicate
 */
class Predicate extends Expression {

    /**
     * Return a negation of this Predicate like ->isNull()->not()
     *
     * @return Predicate::not
     */
    public function not() {
        return new Predicate('not', array($this));
    }
}