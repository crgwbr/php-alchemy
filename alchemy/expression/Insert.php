<?php

namespace Alchemy\expression;
use Exception;


class Insert extends Query {
    protected $into;
    protected $rows = array();


    public function into(Table $table) {
        $this->into = $table;
    }


    public function row() {
        $columns = func_get_args();
        $columns = is_array($columns[0]) ? $columns[0] : $columns;
        $row = array();
        foreach ($columns as $column) {
            if (!$column instanceof Scalar) {
                $column = new Scalar($column);
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
}
