<?php

namespace Alchemy\orm\query;


class DeferredQueryManager {
    protected $session;
    protected $type;
    protected $query;


    public function __construct($type, \Alchemy\orm\Session &$session, $class, $table, array $columns) {
        $type = "Alchemy\\orm\\query\\{$type}";
        $this->type = $type;
        $this->query = new $type($session, $class, $table, $columns);
    }


    public function __call($name, $args) {
        $that = clone $this;
        $method = array($that->query, $name);

        $ret = call_user_func_array($method, $args);
        $type = $this->type;
        if ($ret && $ret instanceof $type) {
            $that->query = $ret;
        } elseif (!is_null($ret)) {
            return $ret;
        }

        return $that;
    }
}
