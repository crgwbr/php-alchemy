<?php

namespace Alchemy\expression;
use Alchemy\util\Monad;


abstract class Query implements IQuery {
    protected $columns = array();
    protected $joins = array();
    protected $where;


    public static function init() {
        $cls = get_called_class();
        return new Monad(new $cls());
    }


    public function column(Value $column) {
       $this->columns[] = $column;
    }


    public function columns() {
        $columns = func_get_args();
        $columns = is_array($columns[0]) ? $columns[0] : $columns;

        foreach ($columns as $column) {
            $this->column($column);
        }
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
}
