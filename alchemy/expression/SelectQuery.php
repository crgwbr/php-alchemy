<?php

namespace Alchemy\expression;
use Exception;


class SelectQuery extends Query {
    protected $columns = array();

    public function __toString() {
        $columns = $this->getColumnSQL();
        $from = $this->getFromSQL();
        $joins = $this->getJoinSQL();
        $where = $this->getWhereSQL();

        $str = "SELECT {$columns} {$from} {$joins} {$where}";

        $str = trim($str);
        return $str;
    }

    public function addColumn(Column $column) {
       $this->columns[] = $column;
    }

    protected function getColumnSQL() {
        if (count($this->columns) <= 0) {
            throw new Exception("No columns to select");
        }

        $columns = array_map(function($column) {
            return $column->getSelectDeclaration();
        }, $this->columns);
        $columns = implode(", ", $columns);
        return $columns;
    }
}
