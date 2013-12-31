<?php

namespace Alchemy\orm;
use Alchemy\expression\Table;
use Alchemy\expression\QueryManager;

class DeferredSelect {
    private $session;
    private $class;
    private $query;


    public function __construct(&$session, $class, Table $table) {
        $this->session = &$session;
        $this->class = $class;

        $query = new QueryManager();
        $query = $query->select($table->listColumns())
                       ->from($table);

        $this->query = $query;
    }


    public function all() {
        return $this->session->execute($this->class, $this->query);
    }
}
