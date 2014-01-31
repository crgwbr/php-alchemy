<?php

namespace Alchemy\expression;


// expressions

Expression::define(null, null, array(
    'arity' => 0,
    'tags' => array(
        'expr.element' => 'expr.value',
        'sql.compile' => 'Expression',
        'expr.value' => true) ));

$expressions = array(
    // numeric
    'add'       => 2,
    'sub'       => 2,
    'mult'      => 2,
    'div'       => 2,
    'mod'       => 2,
    'abs'       => 1,
    'ceil'      => 1,
    'exp'       => 1,
    'floor'     => 1,
    'ln'        => 1,
    'sqrt'      => 1,

    // datetime
    'extract'   => 2,
    'interval'  => 2,
    'now'       => 0,

    // string
    'lower'     => 1,
    'upper'     => 1,
    'convert'   => 2,
    'translate' => 2,
    'concat'    => 2,
    'coalesce'  => -1);

foreach ($expressions as $name => $arity) {
    Expression::define($name, null, array('arity' => $arity));
}


// predicates

Predicate::define(null, null, array(
    'arity' => 0,
    'tags' => array(
        'expr.element' => 'expr.value',
        'sql.compile' => 'Expression',
        'expr.predicate' => true) ));

Predicate::define('and', null, array(
    'arity' => -1,
    'tags' => array(
        'expr.element' => 'expr.predicate') ));

Predicate::define('or', 'and');

Predicate::define('not', 'and', array(
    'arity' => 1));

Expression::define_alias('and', 'Predicate::and');
Expression::define_alias('or',  'Predicate::or');
Expression::define_alias('not', 'Predicate::not');

$predicates = array(
    'equal'     => 2,
    'lt'        => 2,
    'gt'        => 2,
    'ne'        => 2,
    'le'        => 2,
    'ge'        => 2,
    'between'   => 3,
    'isnull'    => 1,
    'like'      => 2,
    'in'        => -1);

foreach ($predicates as $name => $arity) {
    Predicate::define($name, null, array('arity' => $arity));
    Expression::define_alias($name, "Predicate::{$name}");
}
