<?php

namespace Alchemy\expression;

/**
 * Represents a true/false logical predicate
 */
class Predicate extends Expression {
    protected static $element_tag = 'expr.value';
    protected static $result_tag = 'expr.predicate';
    protected static $types = array(
        'equal'     => 2,
        'lt'        => 2,
        'gt'        => 2,
        'ne'        => 2,
        'le'        => 2,
        'ge'        => 2,
        'between'   => 3,
        'isNull'    => 1,
        'like'      => 2,
        'in'        => -1);

    /**
     * Return a negation of this Predicate like ->isNull()->not()
     *
     * @return NegativePredicate
     */
    public function not() {
        return new NegativePredicate('not', array($this));
    }
}


class CompoundPredicate extends Predicate {
    protected static $element_tag = 'expr.predicate';
    protected static $types = array(
        'and'       => -1,
        'or'        => -1);
}


class NegativePredicate extends Predicate {
    protected static $element_tag = 'expr.predicate';
    protected static $types = array(
        'not'       => 1);

    public function not() {
        return $this->elements[0];
    }
}