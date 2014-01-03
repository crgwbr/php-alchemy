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
     */
    public function create($cls) {
        $create = new Create($cls::table());
        $this->session->engine()->query($create);
    }


    /**
     * Find all subclasses of DataMapper and run {DDL::create()} on each
     * of them.
     */
    public function createAll() {
        foreach ($this->listMappers() as $cls) {
            $this->create($cls);
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
        foreach ($this->listMappers() as $cls) {
            $this->drop($cls);
        }
    }


    /**
     * List all subclasses of DataMapper. Only works for
     * classes already loaded by PHP.
     *
     * @return array
     */
    protected function listMappers() {
        $result = array();
        foreach (get_declared_classes() as $cls) {
            if (is_subclass_of($cls, '\Alchemy\orm\DataMapper'))
                $result[] = $cls;
        }

        return $result;
    }
}
