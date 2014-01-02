<?php

namespace Alchemy\orm;
use Alchemy\engine\IEngine;
use Alchemy\expression\Table;
use Alchemy\expression\Column;
use Alchemy\expression\Select;
use Alchemy\expression\Insert;

class Session {
    protected $queue;
    private $engine;
    private $records = array();
    private $updated = array();


    public function __construct(IEngine $engine) {
        $this->engine = $engine;
        $this->queue = new WorkQueue();
    }


    public function add($obj) {
        $cls = get_class($obj);

        if (!array_key_exists($cls, $this->records)) {
            $this->records[$cls] = array();
        }

        $id = count($this->records[$cls]);

        $this->records[$cls][$id] = array();
        $obj->setSession($this, $id);
        $obj->save();

        $this->queue->insert($cls, $this->records[$cls][$id]);
    }


    public function commit() {
        $this->flush();
        $this->engine->commitTransaction();
    }


    public function ddl() {
        return new DDL($this);
    }


    public function engine() {
        return $this->engine;
    }


    public function execute($class, $query) {
        $rows = $this->engine->query($query);
        return $this->wrap($class, $rows);
    }


    public function flush() {
        $this->engine->beginTransaction();
        $this->queue->flush($this->engine);
    }


    public function &getProperty($cls, $id, $prop) {
        return $this->records[$cls][$id][$prop];
    }


    public function objects($cls) {
        return new SessionSelect($this, $cls, $cls::table());
    }


    public function setProperty($cls, $id, $prop, $value) {
        if (isset($this->records[$cls][$id][$prop]) && $this->records[$cls][$id][$prop] === $value) {
            return;
        }

        $this->records[$cls][$id][$prop] = &$value;
        $this->updated[$cls][$id][$prop] = &$value;
    }


    protected function wrap($cls, $rows) {
        $objects = array();
        $table = $cls::table();
        $rows = $rows ?: array();

        if (!array_key_exists($cls, $this->records)) {
            $this->records[$cls] = array();
        }

        foreach ($rows as $row) {
            $i = count($this->records[$cls]);

            $record = array();
            foreach ($row as $column => $value) {
                $record[$column] = $table->$column->decode($value);
            }

            $this->records[$cls][] = $record;
            $objects[] = $cls::from_session($this, $i);
        }

        return $objects;
    }
}
