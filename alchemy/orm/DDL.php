<?php

namespace Alchemy\orm;
use Alchemy\core\query\Query;
use Alchemy\core\schema\Table;


/**
 * Controller for performing DDL operations on the database
 */
class DDL {
    private $session;

    /**
     * Object constructor
     *
     * @param Session $session
     */
    public function __construct(Session $session) {
        $this->session = $session;
    }


    /**
     * CREATE a Table
     *
     * @param Table $table
     */
    public function create($table) {
        $create = Query::Create($table);
        $this->session->engine()->query($create);
    }


    /**
     * Run {@see DDL::create()} on all registered Tables.
     */
    public function createAll() {
        $tables = Table::list_registered();
        $created = array();

        while (count($tables) > 0) {
            $table = array_pop($tables);
            $dependancies = $table->listDependancies();
            $dependancies = array_diff($dependancies, $created);

            if (count($dependancies) > 0) {
                array_unshift($tables, $table);
            } else {
                $this->create($table);
                $created[] = $table->getName();
            }
        }
    }


    /**
     * DROP a Table
     */
    public function drop($table) {
        $drop = Query::Drop($table);
        $this->session->engine()->query($drop);
    }


    /**
     * Run {@see DDL::drop()} on all registered Tables.
     */
    public function dropAll() {
        $tables = Table::list_registered();
        $dropped = array();

        while (count($tables) > 0) {
            $table = array_pop($tables);
            $dependants = $table->listDependants();
            $dependants = array_diff($dependants, $dropped);

            if (count($dependants) > 0) {
                array_unshift($tables, $table);
            } else {
                $this->drop($table);
                $dropped[] = $table->getName();
            }
        }
    }
}
