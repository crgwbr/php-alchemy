<?php

namespace Alchemy\expression;
use Exception;


class Select extends Query {
    protected $from;


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
