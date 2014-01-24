<?php

namespace Alchemy\expression;


/**
 * Represent a Scalar value in SQL
 */
class Scalar extends Element implements IQueryValue {

    protected $value;


    /**
     * Object constructor
     *
     * @param mixed $value Value
     * @param mixed $tag   Optional. Tags will be inferred if not provided
     */
    public function __construct($value, $tag = null) {
        $this->value = $value;
        $this->addTag("expr.value", $tag ?: self::infer_type($value));
        $this->addTag("sql.compile", "Scalar");
    }


    public function getParameters() {
        return array($this);
    }


    /**
     * Get the primitive value
     *
     * @param mixed
     */
    public function getValue() {
        return $this->value;
    }


    /**
     * Infer the type of a given value
     *
     * @param  mixed $value
     * @return string
     */
    protected static function infer_type($value) {
        static $types = array('boolean', 'integer', 'null', 'string');

        $type = strtolower(gettype($value));
        return in_array($type, $types) ? $type : 'string';
    }
}
