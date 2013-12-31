<?php

namespace Alchemy\orm;
use Alchemy\expression\Table;


class DeferredQueryManager {
    protected $session;
    protected $queryType;
    protected $query;


    public function __construct($queryType, Session &$session, $mapper, Table $table) {
        $queryType = "Alchemy\\orm\\{$queryType}";
        $this->queryType = $queryType;
        $this->query = new $queryType($session, $mapper, $table);
    }


    public function __call($name, $args) {
        $that = clone $this;
        $method = array($that->query, $name);

        $ret = call_user_func_array($method, $args);
        $queryType = $this->queryType;
        if ($ret && $ret instanceof $queryType) {
            $that->query = $ret;
        } elseif (!is_null($ret)) {
            return $ret;
        }

        return $that;
    }
}
