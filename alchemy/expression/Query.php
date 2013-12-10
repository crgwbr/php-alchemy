<?php

namespace Alchemy\expression;


class Query {
    protected $joins = array();
    protected $where;


    public function __toString() {
        throw new Exception("__toString functionality should be overridden in a subclass");
    }


    public function __call($name, $args) {
        $name = ucfirst($name);
        $class = "Alchemy\\expression\\{$name}Query";
        if (class_exists($class)) {
            $args = count($args) > 0 && is_array($args[0]) ? $args[0] : $args;
            return $this->changeQueryType($class, $args);
        }

        throw new \BadMethodCallException("Class {$class} not found");
    }


    protected function changeQueryType($class, $columns) {
        $query = new $class();
        foreach ($columns as $column) {
            $query->column($column);
        }

        return $query;
    }


    public function getParameters() {
        return array();
    }


    public function join(Table $table, Expression $on, $direction = null, $type = null) {
        $direction = $direction ?: Join::LEFT;
        $type = $type ?: Join::INNER;
        $this->joins[] = new Join($direction, $type, $table, $on);
    }


    public function outerJoin(Table $table, Expression $on, $direction = null) {
        return $this->join($table, $on, $direction, Join::OUTER);
    }


    public function where(Expression $expr) {
       $this->where = $expr;
    }


    protected function getJoinSQL() {
        return implode(" ", $this->joins);
    }


    protected function getWhereSQL() {
        if (empty($this->where)) {
            return "";
        }

        return "WHERE {$this->where}";
    }
}
