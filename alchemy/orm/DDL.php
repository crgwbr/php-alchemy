<?php

namespace Alchemy\orm;
use Alchemy\expression\Create;
use Alchemy\expression\Drop;


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
     * CREATE the table for the given DataMapper class
     *
     * @param string $cls Class Name of DataMapper child
     */
    public function create($cls) {
        $create = new Create($cls::table());
        $this->session->engine()->query($create);
    }


    /**
     * Find all subclasses of DataMapper and run {@see DDL::create()} on each
     * of them.
     */
    public function createAll() {
        $mappers = DataMapper::list_mappers();
        $created = array();

        while (count($mappers) > 0) {
            $mapper = array_pop($mappers);
            $table = $mapper::table();
            $dependancies = $table->listDependancies();
            $dependancies = array_diff($dependancies, $created);

            if (count($dependancies) > 0) {
                array_unshift($mappers, $mapper);
            } else {
                $this->create($mapper);
                $created[] = $table->getName();
            }
        }
    }


    /**
     * DROP the table for the given DataMapper class
     */
    public function drop($cls) {
        $drop = new Drop($cls::table());
        $this->session->engine()->query($drop);
    }


    /**
     * Find all subclasses of DataMapper and run {DDL::drop()} on each
     * of them.
     */
    public function dropAll() {
        $mappers = DataMapper::list_mappers();
        $dropped = array();

        while (count($mappers) > 0) {
            $mapper = array_pop($mappers);
            $table = $mapper::table();
            $dependants = $table->listDependants();
            $dependants = array_diff($dependants, $dropped);

            if (count($dependants) > 0) {
                array_unshift($mappers, $mapper);
            } else {
                $this->drop($mapper);
                $dropped[] = $table->getName();
            }
        }
    }
}
