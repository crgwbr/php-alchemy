<?php

namespace Alchemy\expression;


class QueryManager {
    protected $query;


    public function __construct() {
       $this->query = new Query();
    }


    public function __call($name, $args) {
        $that = clone $this;
        $method = array($that->query, $name);

        $ret = call_user_func_array($method, $args);
        if ($ret && $ret instanceof Query) {
            $that->query = $ret;
        } elseif (!is_null($ret)) {
            return $ret;
        }

        return $that;
    }


    public function __toString() {
        return (string)$this->query;
    }
}
