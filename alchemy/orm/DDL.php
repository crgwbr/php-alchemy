<?php

namespace Alchemy\orm;
use Alchemy\expression\Create;
use Alchemy\expression\Drop;


class DDL {
    private $session;


    public function __construct(&$session) {
        $this->session = &$session;
    }


    public function create($cls) {
        $create = new Create($cls::table());
        $this->session->engine()->query($create);
    }


    public function createAll() {
        foreach ($this->listMappers() as $cls) {
            $this->create($cls);
        }
    }


    public function drop($cls) {
        $drop = new Drop($cls::table());
        $this->session->engine()->query($drop);
    }


    public function dropAll() {
        foreach ($this->listMappers() as $cls) {
            $this->drop($cls);
        }
    }


    public function listMappers() {
        $result = array();
        foreach (get_declared_classes() as $cls) {
            if (is_subclass_of($cls, '\Alchemy\orm\DataMapper'))
                $result[] = $cls;
        }

        return $result;
    }
}
