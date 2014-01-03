<?php

namespace Alchemy\orm;
use Alchemy\engine\IEngine;
use Alchemy\engine\ResultSet;
use Alchemy\expression\Insert;
use Alchemy\expression\CompoundExpression;
use Alchemy\util\Promise;


class WorkQueue {
    protected $queue = array();


    public function flush(IEngine $engine) {
        while ($item = array_shift($this->queue)) {
            list($query, $promise) = $item;
            $r = $engine->query($query);
            $promise->resolve($r);
        }
    }


    public function insert($cls, $data) {
        $table = $cls::table();

        $scalars = array();
        $columns = array();
        foreach ($data as $name => $value) {
            $columns[] = $table->$name;
            $scalars[] = $table->$name->encode($value);
        }

        $query = Insert::init()->columns($columns)
                               ->into($table)
                               ->row($scalars);
        return $this->push($query);
    }


    protected function push($query) {
        $promise = new Promise();
        $this->queue[] = array($query, $promise);
        return $promise;
    }


    public function update($cls, $pk, $data) {
        $table = $cls::table();

        $query = Update::init()->table($table);
        foreach ($data as $name => $value) {
            $query = $query->set($table->$name, $table->$name->encode($value));
        }

        $where = null;
        foreach ($pk as $name => $value) {
            if (!$where) {
                $where = $table->$name->equal($value);
            } else {
                $where = $where->and($table->$name->equal($value));
            }
        }
        $query = $query->where($where);

        return $this->push($query);
    }
}
