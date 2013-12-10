<?php

namespace Alchemy\expression;


class Table {
    protected static $tableCounter = 0;

    protected $name;
    protected $alias;


    public function __construct($name) {
       $this->name = $name;
       $this->alias = strtolower(substr($name, 0, 2)) . (++static::$tableCounter);
    }


    public function __toString() {
        return "{$this->getName()} {$this->getAlias()}";
    }


    public function getName() {
        return $this->name;
    }


    public function getAlias() {
        return $this->alias;
    }
}
