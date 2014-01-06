<?php

namespace Alchemy\expression;


/**
 * Abstract base class for representing a column in SQL
 */
abstract class Column implements IQueryValue {
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


    /**
     * Get the combined list of self::$default_kwargs form the
     * inheritance tree
     *
     * @return array
     */
    public static function get_default_kwargs() {
        $cls = get_called_class();
        $kwargs = $cls::$default_kwargs;

        $parent = get_parent_class($cls);
        if ($parent && is_callable(array($parent, 'get_default_kwargs'))) {
            return array_merge($parent::get_default_kwargs(), $kwargs);
        }

        return $kwargs;
    }


    /**
     * Object Constructor
     *
     * @param string $tableAlias
     * @param string $name
     * @param string $alias
     * @param array $args
     * @param array $kwargs
     */
    public function __construct($tableAlias, $name, $alias, array $args, array $kwargs) {
        $this->tableAlias = $tableAlias;
        $this->name = $name;
        $this->alias = $alias;
        $this->args = array_merge(static::$default_args, $args);
        $this->kwargs = array_merge(static::get_default_kwargs(), $kwargs);
    }


    /**
     * Build and return a BinaryExpression by comparing this
     * column to another IQueryValue
     *
     * @param $name Operator Name: and, or
     * @param $args array([0] => IQueryValue, ...) IQueryValue to compare to
     */
    public function __call($name, $args) {
        $value = $args[0];
        if (!$value instanceof IQueryValue) {
            $value = new Scalar($value);
        }

        return new BinaryExpression($this, Operator::$name(), $value);
    }


    /**
     * Decode a value from the RDBMS into a PHP value
     *
     * @param mixed $value
     * @return mixed
     */
    abstract public function decode($value);


    /**
     * Encode a PHP value into something usable for the RDBMS.
     *
     * @param mixed $value
     * @return Scalar
     */
    abstract public function encode($value);


    /**
     * Return true if this column has an index on it. This doesn't
     * apply to multi-column indexes, only single column indexes.
     *
     * @return bool
     */
    public function hasIndex() {
        return $this->kwargs['index'];
    }


    /**
     * Return true if this column is part of the primary key
     *
     * @return bool
     */
    public function isPrimaryKey() {
        return $this->kwargs['primary_key'];
    }


    /**
     * Return true if this column has a unique index on it. This doesn't
     * apply to multi-column indexes, only single column indexes.
     *
     * @return bool
     */
    public function isUnique() {
        return $this->kwargs['unique'];
    }
}
