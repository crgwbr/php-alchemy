<?php

namespace Alchemy\expression;


/**
 * Abstract base class for representing a column in SQL
 */
abstract class Column implements IQueryValue {
    protected static $default_args = array(
        'default' => null,
        'index' => false,
        'null' => false,
        'primary_key' => false,
        'unique' => false,
    );

    protected $table;
    protected $name;
    protected $alias;
    protected $args;


    /**
     * Get the combined list of self::$default_kwargs form the
     * inheritance tree
     *
     * @return array
     */
    public static function get_default_args() {
        $cls = get_called_class();
        $args = $cls::$default_args;

        $parent = get_parent_class($cls);
        if ($parent && is_callable(array($parent, 'get_default_args'))) {
            return array_merge($parent::get_default_args(), $args);
        }

        return $args;
    }


    /**
     * Object Constructor
     *
     * @param array $args
     */
    public function __construct(array $args = array()) {
        $this->args = $args + static::get_default_args();
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


    public function assign(Table $table = null, $name = '', $alias = '') {
        $this->table = $table;
        $this->name = $name;
        $this->alias = $alias;
    }


    /**
     * Decode a value from the RDBMS into a PHP value
     *
     * @param mixed $value
     * @return string
     */
    public function decode($value) {
        return (string)$value;
    }


    /**
     * Encode a PHP value into something usable for the RDBMS.
     *
     * @param mixed $value
     * @return Scalar
     */
    public function encode($value) {
        return new Scalar((string)$value, Scalar::T_STR);
    }


    public function getAlias() {
        return $this->alias ?: "";
    }


    public function getName() {
        return $this->name ?: "";
    }


    public function getTable() {
        return $this->table;
    }


    /**
     * Return true if this column has an index on it. This doesn't
     * apply to multi-column indexes, only single column indexes.
     *
     * @return bool
     */
    public function hasIndex() {
        return $this->args['index'];
    }


    /**
     * Return true if this column can not be null
     *
     * @return bool
     */
    public function isNotNull() {
        return !$this->args['null'];
    }


    /**
     * Return true if this column is part of the primary key
     *
     * @return bool
     */
    public function isPrimaryKey() {
        return $this->args['primary_key'];
    }


    /**
     * Return true if this column has a unique index on it. This doesn't
     * apply to multi-column indexes, only single column indexes.
     *
     * @return bool
     */
    public function isUnique() {
        return $this->args['unique'];
    }
}
