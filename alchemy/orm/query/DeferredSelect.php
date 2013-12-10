<?php

namespace Alchemy\orm\query;
use Alchemy\expression\Table;
use Alchemy\expression\QueryManager;

class DeferredSelect {
    private $session;
    private $class;
    private $table;
    private $columns;
    private $query;


    public function __construct(&$session, $class, $table, array $columns) {
        $this->session = &$session;
        $this->class = $class;
        $this->table = new Table($table);
        $this->columns = $columns;

        $query = new QueryManager();
        $query = $query->select();
        foreach ($columns as $c) {
            $query = $c->modifySelect($this->table, $query);
        }

        $query = $query->from($this->table);
        $this->query = $query;
    }


    public function all() {
        return $this->session->execute($this->class, $this->query);
    }
}
