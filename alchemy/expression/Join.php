<?php

namespace Alchemy\expression;


class Join {
    const LEFT = 'LEFT';
    const RIGHT = 'RIGHT';
    const FULL = 'FULL';
    const INNER = 'INNER';
    const OUTER = 'OUTER';

    protected $direction;
    protected $type;
    protected $table;
    protected $on;

    public function __construct($direction, $type, Table $table, Expression $on) {
        $this->direction = $direction;
        $this->type = $type;
        $this->table = &$table;
        $this->on = &$on;
    }

    public function __toString() {
        return "{$this->direction} {$this->type} JOIN {$this->table} ON {$this->on}";
    }
}
