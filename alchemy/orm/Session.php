<?php

namespace Alchemy\orm;
use Alchemy\engine\IEngine;
use Alchemy\expression\Table;
use Alchemy\expression\Column;
use Alchemy\expression\QueryManager;

class Session {

    private $engine;
    private $records = array();
    private $added = array();
    private $updated = array();


    public function __construct(IEngine $engine) {
        $this->engine = $engine;
    }


    public function add($obj) {
        $cls = get_class($obj);

        if (!array_key_exists($cls, $this->records)) {
            $this->records[$cls] = array();
        }

        $id = count($this->records[$cls]);

        $this->records[$cls][] = array();
        $this->added[$cls][] = $id;

        $obj->setSession($this, $id);
        $obj->save();
    }


    public function commit() {
        foreach ($this->added as $cls => $added) {
            foreach ($added as $id) {
                $this->insert($cls, $this->updated[$cls][$id]);
                unset($this->updated[$cls][$id]);
            }

            $this->added[$cls] = array();
        }
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


    public function &getProperty($cls, $id, $prop) {
        return $this->records[$cls][$id][$prop];
    }


    protected function insert($cls, $data) {
        $table = $cls::table();

        $scalars = array();
        $columns = array();
        foreach ($data as $name => $value) {
            $columns[] = $table->$name;
            $scalars[] = $table->$name->encode($value);
        }

        $query = new QueryManager();
        $query = $query->insert($columns)
                       ->into($table)
                       ->row($scalars);
        $this->engine->query($query);
    }


    public function objects($cls) {
        $query = new DeferredQueryManager(
            'DeferredSelect',
            $this,
            $cls,
            $cls::table()
        );

        return $query;
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
