<?php

namespace Alchemy\orm;
use Alchemy\engine\IEngine;
use Alchemy\expression\Insert;


class WorkQueue {
    protected $queue = array();


    public function flush(IEngine $engine) {
        while ($query = array_shift($this->queue)) {
            $engine->query($query);
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
        $this->push($query);
    }


    protected function push($query) {
        $this->queue[] = $query;
    }
}
