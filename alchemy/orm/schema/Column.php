<?php

namespace Alchemy\orm\schema;

use Alchemy\expression\Table;
use Alchemy\expression\QueryManager;


abstract class Column {
    protected static $default_args = array();
    protected static $default_kwargs = array(
        'index' => false,
        'null' => false,
        'primary_key' => false,
        'unique' => false,
    );


    protected $name;
    protected $args;
    protected $kwargs;


    public function __construct($name, array $args, array $kwargs) {
        $this->name = $name;
        $this->args = array_merge(static::$default_args, $args);
        $this->kwargs = array_merge(static::$default_kwargs, $kwargs);
    }


    abstract public function columnDefinition();


    abstract public function decode($value);


    abstract public function encode($value);


    public function hasIndex() {
        if (!$this->kwargs['index']) {
            return false;
        }

        return $this->kwargs['index'] === true
           ? $this->name
           : $this->kwargs['index'];
    }


    public function hasUniqueIndex() {
        if (!$this->kwargs['unique']) {
            return false;
        }

        return $this->kwargs['unique'] === true
           ? $this->name
           : $this->kwargs['unique'];
    }


    public function isPrimaryKey() {
        return $this->kwargs['primary_key'];
    }


    public function modifySelect(Table $table, QueryManager $query) {
        $column = new \Alchemy\expression\Column($table, $this->name);
        return $query->column($column);
    }
}
