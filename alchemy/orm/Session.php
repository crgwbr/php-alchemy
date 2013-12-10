<?php

namespace Alchemy\orm;
use Alchemy\orm\query\DeferredQueryManager;
use Alchemy\orm\ddl\DDL;
use Alchemy\expression\Table;
use Alchemy\expression\Column;
use Alchemy\expression\QueryManager;

class Session {

    private $engine;
    private $records = array();
    private $added = array();
    private $updated = array();


    public function __construct($engine) {
        $this->engine = $engine;
    }


    public function add($obj) {
        $cls = get_class($obj);
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
        $schema = $cls::schema_definition();
        $table = new Table($cls::table_name());
        $columns = array();
        $scalars = array();
        foreach ($data as $name => $value) {
            $columns[] = new Column($table, $name);
            $scalars[] = $schema[$name]->encode($value);
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
            $cls::table_name(),
            $cls::schema_definition()
        );

        return $query;
    }


    public function setProperty($cls, $id, $prop, $value) {
        if ($this->records[$cls][$id][$prop] === $value) {
            return;
        }

        $this->records[$cls][$id][$prop] = &$value;
        $this->updated[$cls][$id][$prop] = &$value;
    }


    protected function wrap($cls, $rows) {
        $objects = array();
        $schema = $cls::schema_definition();

        foreach ($rows as $row) {
            $i = count($this->records[$cls]);

            $record = array();
            foreach ($row as $column => $value) {
                $record[$column] = $schema[$column]->decode($value);
            }

            $this->records[$cls][] = $record;
            $objects[] = $cls::from_session($this, $i);
        }

        return $objects;
    }
}
