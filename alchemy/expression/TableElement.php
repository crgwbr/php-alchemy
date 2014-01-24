<?php

namespace Alchemy\expression;


/**
 * Class for representing an index in SQL
 */
abstract class TableElement extends Element {
    protected static $default_args = array();

    protected $args;
    protected $table;
    protected $name;


    /**
     * Get the combined list of self::$default_args form the
     * inheritance tree
     *
     * @return array
     */
    protected static function get_default_args() {
        $cls = get_called_class();
        $args = $cls::$default_args;

        $parent = get_parent_class($cls);
        if ($parent && is_callable(array($parent, 'get_default_args'))) {
            return array_merge($parent::get_default_args(), $args);
        }

        return $args;
    }


    /**
     * Convert an argument's structure to be similar to the default
     * ie. (5, array(array())) -> array(array(5))
     *
     * @return mixed
     */
    protected static function normalize_arg($arg, $default) {
        if (is_array($default)) {
            if (!is_array($arg)) {
                $arg = !is_null($arg) ? array($arg) : $default;
            }

            foreach ($default as $k => $v) {
                $arg[$k] = self::normalize_arg(array_key_exists($k, $arg) ? $arg[$k] : null, $v);
            }
        }

        return $arg ?: $default;
    }


    /**
     * Object Constructor
     *
     * @param array $args
     */
    public function __construct($args = array(), $table = null, $name = '') {
        $this->name = $name;
        $this->table = $table;
        $this->args = self::normalize_arg($args, static::get_default_args());

        $parts = explode('\\', get_called_class());
        $cls = array_pop($parts);
        $this->addTag("sql.create", $cls);
    }


    /**
     * Duplicates this, but possibly as a different class
     *
     * @return TableElement
     */
    public function copy(array $args = array(), $table = null, $name = '') {
        return new static($args + $this->args, $table, $name);
    }


    public function getName() {
        return $this->name ?: "";
    }


    public function getTable() {
        return $this->table;
    }
}