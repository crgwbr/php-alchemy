<?php

namespace Alchemy\orm;
use Alchemy\engine\IEngine;
use Alchemy\engine\ResultSet;
use Alchemy\expression\Table;
use Alchemy\expression\Column;
use Alchemy\expression\Select;
use Alchemy\expression\Insert;

class Session {
    protected $queue;
    protected $engine;
    protected $records = array();
    protected $updated = array();


    public function __construct(IEngine $engine) {
        $this->engine = $engine;
        $this->queue = new WorkQueue();
    }


    public function add(DataMapper $obj) {
        $cls = get_class($obj);

        if (!array_key_exists($cls, $this->records)) {
            $this->records[$cls] = array();
        }

        $tempID = $this->getPrimaryKey($cls);

        $this->records[$cls][$tempID] = array();
        $obj->setSession($this, $tempID);
        $obj->save(false);

        $inserting = $this->queue->insert($obj, $this->records[$cls][$tempID]);

        $self = $this;
        $inserting->done(function(ResultSet $r) use ($self, $obj, $tempID) {
            $self->updatePrimaryKey($obj, $tempID, $r->lastInsertID());
        });

        unset($this->updated[$cls][$tempID]);

        return $inserting;
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


    protected function getPrimaryKey($cls, $record = array()) {
        $pk = array();
        foreach ($cls::table()->listPrimaryKeyComponents() as $name => $column) {
            if (isset($record[$name])) {
                $pk[] = $record[$name];
            }
        }
        $pk = implode("-", $pk);

        // PK probably hasn't been allocated by the DB yet
        // Create a temporary one to identify it's record
        // in the session
        if (empty($pk)) {
            $pk = "TRANSIENT-KEY-" . rand();
        }

        return $pk;
    }


    public function &getProperty($cls, $id, $prop) {
        return $this->records[$cls][$id][$prop];
    }


    public function objects($cls) {
        return new SessionSelect($this, $cls, $cls::table());
    }


    public function save($cls, $id) {
        if (empty($this->updated[$cls][$id])) {
            return;
        }

        $pk = array();
        foreach ($cls::table()->listPrimaryKeyComponents() as $name => $column) {
            $pk[$name] = $this->updated[$cls][$id][$name];
        }

        $updating = $this->queue->update($cls, $pk, $this->updated[$cls][$id]);
        unset($this->updated[$cls][$id]);

        return $updating;
    }


    public function setProperty($cls, $id, $prop, $value) {
        if (isset($this->records[$cls][$id][$prop]) && $this->records[$cls][$id][$prop] === $value) {
            return;
        }

        $this->records[$cls][$id][$prop] = &$value;
        $this->updated[$cls][$id][$prop] = &$value;
    }


    public function updatePrimaryKey($obj, $oldID, $newID) {
        $cls = get_class($obj);

        // Update the auto increment column
        foreach ($cls::table()->listPrimaryKeyComponents() as $name => $column) {
            if (!isset($this->records[$cls][$oldID][$name])) {
                $this->records[$cls][$oldID][$name] = $newID;
            }
        }

        // Copy the record to it's new address
        $storeID = $this->getPrimaryKey($cls, $this->records[$cls][$oldID]);
        $this->records[$cls][$storeID] = $this->records[$cls][$oldID];

        // Tell the object it's new address
        $obj->setSession($this, $storeID);

        // Remove the transient record
        unset($this->records[$cls][$oldID]);
        unset($this->updated[$cls][$oldID]);
    }


    protected function wrap($cls, $rows) {
        $objects = array();
        $table = $cls::table();
        $rows = $rows ?: array();

        if (!array_key_exists($cls, $this->records)) {
            $this->records[$cls] = array();
        }

        foreach ($rows as $row) {
            $pk = $this->getPrimaryKey($cls, $row);

            $record = array();
            foreach ($row as $column => $value) {
                $record[$column] = $table->$column->decode($value);
            }

            $this->records[$cls][$pk] = $record;
            $objects[] = $cls::from_session($this, $pk);
        }

        return $objects;
    }
}
