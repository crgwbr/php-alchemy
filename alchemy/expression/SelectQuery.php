<?php

namespace Alchemy\expression;
use Exception;


class SelectQuery extends Query {
    protected $columns = array();
    protected $from;


    public function column(Value $column) {
       $this->columns[] = $column;
    }


    public function from(Table $table) {
        $this->from = $table;
    }


    public function getParameters() {
        $params = array();

        foreach ($this->columns as $column) {
            if ($column instanceof Scalar) {
                $params[] = $column;
            }
        }

        foreach ($this->joins as $join) {
            $params = array_merge($params, $join->getParameters());
        }

        $params = $this->where
            ? array_merge($params, $this->where->getParameters())
            : $params;

        return $params;
    }
}
