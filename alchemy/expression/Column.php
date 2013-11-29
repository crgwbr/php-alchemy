<?php

namespace Alchemy\expression;


class Column extends Value {
    protected $table;
    protected $columnName;
    protected $columnAlias;

    public function __construct(Table $table, $name, $alias = "") {
       $this->table = $table;
       $this->columnName = $name;
       $this->columnAlias = $alias ?: $name;
    }

    public function __toString() {
        $tableAlias = $this->table->getAlias();
        return "{$tableAlias}.{$this->columnName}";
    }

    public function getName() {
        return $this->columnName;
    }

    public function getSelectDeclaration() {
        return "{$this} as {$this->columnAlias}";
    }
}
