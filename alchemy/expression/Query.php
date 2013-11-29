<?php

namespace Alchemy\expression;


class Query {
    protected $joins = array();
    protected $where;

    public function __toString() {
        return "";
    }

    public function insert() {
        $query = new InsertQuery();
        foreach (func_get_args() as $column) {
            $query->column($column);
        }
        return $query;
    }

    public function join(Table $table, Expression $on, $direction = null, $type = null) {
        $direction = $direction ?: Join::LEFT;
        $type = $type ?: Join::INNER;
        $this->joins[] = new Join($direction, $type, $table, $on);
    }

    public function select() {
        $query = new SelectQuery();
        foreach (func_get_args() as $column) {
            $query->column($column);
        }
        return $query;
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
