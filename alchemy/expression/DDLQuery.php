<?php

namespace Alchemy\expression;


abstract class DDLQuery implements IQuery {
    protected $table;

    public function __construct(Table $table) {
        $this->table = $table;
    }

    public function getParameters() {
        return array();
    }
}
