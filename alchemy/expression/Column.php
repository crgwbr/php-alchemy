<?php

namespace Alchemy\expression;


abstract class Column extends Value {
    protected static $default_args = array();
    protected static $default_kwargs = array(
        'default' => null,
        'index' => false,
        'null' => false,
        'primary_key' => false,
        'unique' => false,
    );

    protected $tableAlias;
    protected $name;
    protected $alias;
    protected $args;
    protected $kwargs;


    public static function get_default_kwargs() {
        $cls = get_called_class();
        $kwargs = $cls::$default_kwargs;

        $parent = get_parent_class($cls);
        if ($parent && is_callable(array($parent, 'get_default_kwargs'))) {
            return array_merge($parent::get_default_kwargs(), $kwargs);
        }

        return $kwargs;
    }


    public function __construct($tableAlias, $name, $alias, array $args, array $kwargs) {
        $this->tableAlias = $tableAlias;
        $this->name = $name;
        $this->alias = $alias;
        $this->args = array_merge(static::$default_args, $args);
        $this->kwargs = array_merge(static::get_default_kwargs(), $kwargs);
    }


    public function __call($name, $args) {
        $value = $args[0];
        if (!$value instanceof Value) {
            $value = new Scalar($value);
        }

        return new BinaryExpression($this, Operator::$name(), $value);
    }


    abstract public function decode($value);


    abstract public function encode($value);
}
