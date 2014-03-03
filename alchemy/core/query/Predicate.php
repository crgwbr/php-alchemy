<?php

namespace Alchemy\core\query;

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


    /**
     * Return an AND-merge of all predicates given as arguments or arrays
     *
     * @param  Predicate $expr a Predicate or array of Predicates to include
     *                         ...
     * @return Predicate::AND
     */
    public static function all() {
        $elements = self::flatten(func_get_args());
        return count($elements) == 1 ? $elements[0] : new Predicate('and', $elements);
    }


    protected static function flatten(array $args) {
        $elements = array();

        foreach($args as $arg) {
            $new = array();

            if (is_array($arg)) {
                $new = self::flatten($arg);
            } elseif ($arg instanceof Predicate) {
                $new = ($arg->getType() == 'and') ? self::flatten($arg->elements()) : array($arg);
            }

            $elements = array_merge($elements, $new);
        }

        return $elements;
    }
}
