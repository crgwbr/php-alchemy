<?php

namespace Alchemy\expression;
use Exception;


class InsertQuery extends Query {
    protected $columns = array();
    protected $into;
    protected $rows = array();


    public function __toString() {
        $columns = $this->getColumnSQL();
        $rows = $this->getRowSQL();

        $str = "INSERT INTO {$this->into->getName()} ($columns) VALUES {$rows}";

        $str = trim($str);
        return $str;
    }


    public function column(Column $column) {
       $this->columns[] = $column;
    }


    public function into(Table $table) {
        $this->into = $table;
    }


    public function row() {
        $columns = func_get_args();
        $columns = is_array($columns[0]) ? $columns[0] : $columns;
        $row = array();
        foreach ($columns as $column) {
            if (!$column instanceof Scalar) {
                throw new Exception("All arguments must be instances of Scalar");
            }

            $row[] = $column;
        }

        $this->rows[] = $row;
    }


    public function getParameters() {
        $params = array();
        foreach ($this->rows as $row) {
            foreach ($row as $value) {
                $params[] = $value;
            }
        }

        return $params;
    }


    protected function getColumnSQL() {
        if (count($this->columns) <= 0) {
            throw new Exception("No columns to insert");
        }

        $columns = array_map(function($column) {
            return $column->getName();
        }, $this->columns);
        $columns = implode(", ", $columns);
        return $columns;
    }


    protected function getRowSQL() {
        if (count($this->rows) <= 0) {
            throw new Exception("No rows to insert");
        }

        $rows = array();
        foreach ($this->rows as $row) {
            $rows[] = implode(", ", $row);
        }
        $rows = implode("), (", $rows);
        return "({$rows})";
    }
}
