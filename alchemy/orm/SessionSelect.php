<?php

namespace Alchemy\orm;
use Alchemy\expression\Select;
use Alchemy\expression\Table;
use Alchemy\util\Monad;


/**
 * Extension of Monad to allow building a SELECT statement while
 * retaining a reference to the Session you started with
 */
class SessionSelect extends Monad {
    protected $session;
    protected $mapper;


    /**
     * Object constructor.
     *
     * @param Session $session Session to use for running select
     * @param string $mapper DataMapper class to return objects as
     */
    public function __construct(Session $session, $mapper) {
        $this->session = $session;
        $this->mapper = $mapper;

        $table = $mapper::table();

        $this->value = new Select();
        $this->value->columns($table->listColumns());
        $this->value->from($table);
    }


    /**
     * Execute the query and return a set of all results
     *
     * @return array
     */
    public function all() {
        return $this->session->execute($this->mapper, $this->value);
    }

    public function first() {
        $all = $this->all();

        if (count($all) == 0) {
            throw new \Exception("Expected at least 1 row, got 0");
        }

        return $all[0];
    }


    public function one() {
        $all = $this->all();

        if (count($all) != 1) {
            throw new \Exception("Expected 1 row, got " . count($all));
        }

        return $all[0];
    }
}
