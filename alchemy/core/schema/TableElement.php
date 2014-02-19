<?php

namespace Alchemy\core\schema;
use Alchemy\core\Element;


/**
 * Class for representing an index in SQL
 */
abstract class TableElement extends Element {

    protected $args;
    protected $table;
    protected $name;


    /**
     * Object Constructor
     *
     * @param array $args
     */
    public function __construct($type, $args = array(), $table = null, $name = '') {
        parent::__construct($type);

        $this->name = $name;
        $this->table = $table;
        $def = static::get_definition($this->type);
        $this->args = self::normalize_arg($args, $def['defaults']);

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
        return new static($this->type, $args + $this->args, $table, $name);
    }


    public function getName() {
        return $this->name ?: "";
    }


    public function getTable() {
        return $this->table;
    }
}