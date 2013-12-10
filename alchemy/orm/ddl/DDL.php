<?php

namespace Alchemy\orm\ddl;


class DDL {
    private $session;


    public function __construct(&$session) {
        $this->session = &$session;
    }


    public function create($cls) {
        $create = new Create($cls);
        $create->execute($this->session->engine());
    }


    public function createAll() {
        foreach ($this->listMappers() as $cls) {
            $this->create($cls);
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
