<?php

namespace Alchemy\expression;

/**
 * Represent an operation on particular elements
 */
class Operation extends Expression implements IQueryValue {
    protected static $element_tag = 'expr.value';
    protected static $result_tag = 'expr.value';
    protected static $types = array(
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
}
