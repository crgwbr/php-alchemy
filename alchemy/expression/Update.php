<?php

namespace Alchemy\expression;
use Exception;


class Update extends Query {
    protected $table;
    protected $values = array();


    public function table(Table $table) {
        $this->table = $table;
    }


    public function set(Column $column, $value) {
        if (!$value instanceof Scalar) {
            $value = new Scalar($value);
        }

        $this->values[$column] = $value;
    }


    public function getParameters() {
        return $this->values;
    }
}
