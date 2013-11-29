<?php

namespace Alchemy\expression;


class Query {
    protected $from;
    protected $joins = array();
    protected $where;

    public function __toString() {
        return "";
    }

    public function from(Table $table) {
        $this->from = $table;
    }

    public function outerJoin(Table $table, Expression $on, $direction = null) {
        return $this->join($table, $on, $direction, Join::OUTER);
    }

    public function join(Table $table, Expression $on, $direction = null, $type = null) {
        $direction = $direction ?: Join::LEFT;
        $type = $type ?: Join::INNER;
        $this->joins[] = new Join($direction, $type, $table, $on);
    }

    public function select() {
        $query = new SelectQuery();
        foreach (func_get_args() as $column) {
            $query->addColumn($column);
        }
        return $query;
    }

    public function where(Expression $expr) {
       $this->where = $expr;
    }

    protected function getFromSQL() {
        if (empty($this->from)) {
            return "";
        }

        return "FROM {$this->from}";
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
