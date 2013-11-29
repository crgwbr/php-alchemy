<?php

namespace Alchemy\expression;
use Exception;


class SelectQuery extends Query {
    protected $columns = array();
    protected $from;

    public function __toString() {
        $columns = $this->getColumnSQL();
        $from = $this->getFromSQL();
        $joins = $this->getJoinSQL();
        $where = $this->getWhereSQL();

        $str = "SELECT {$columns} {$from} {$joins} {$where}";

        $str = trim($str);
        return $str;
    }

    public function column(Column $column) {
       $this->columns[] = $column;
    }

    public function from(Table $table) {
        $this->from = $table;
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

    protected function getFromSQL() {
        if (empty($this->from)) {
            throw new Exception("No 'from' table has been set");
        }

        return "FROM {$this->from}";
    }
}
