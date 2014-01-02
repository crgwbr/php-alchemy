<?php


namespace Alchemy\orm;
use Alchemy\expression\Select;
use Alchemy\expression\Table;
use Alchemy\util\Monad;


class SessionSelect extends Monad {
    protected $session;
    protected $mapper;


    public function __construct(Session &$session, $mapper, Table $table) {
        $this->session = &$session;
        $this->mapper = $mapper;

        $this->value = new Select();
        $this->value->columns($table->listColumns());
        $this->value->from($table);
    }


    public function all() {
        return $this->session->execute($this->mapper, $this->value);
    }
}