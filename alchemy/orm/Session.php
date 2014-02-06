<?php

namespace Alchemy\orm;
use Alchemy\core\schema\Table;
use Alchemy\core\schema\Column;
use Alchemy\core\query\Select;
use Alchemy\core\query\Insert;
use Alchemy\engine\IEngine;
use Alchemy\engine\ResultSet;
use Alchemy\util\Promise;
use Exception;


/**
 * Acts as a controller between domain objects, the SQL expression language, and
 * the RDBMS Engine layer. Manages queuing of work and transactions
 */
class Session {
    protected $queue;
    protected $engine;
    protected $records = array();
    protected $updated = array();


    /**
     * Object constructor
     *
     * @param IEngine $engine
     */
    public function __construct(IEngine $engine) {
        $this->engine = $engine;
        $this->queue = new WorkQueue();
    }


    /**
     * Add a new domain object to the session
     *
     * @param DataMapper $obj
     * @return Promise Resolved when INSERT is sent to the database
     */
    public function add(DataMapper $obj) {
        $cls = get_class($obj);

        // Get the primary key for this object. This is most likely
        // transient, until the DB replaces it with an AutoInc value
        $tempID = $this->getPrimaryKey($cls);

        // Configure the object to use this session as it's data source
        $obj->setSession($this, $tempID);
        $obj->save(false);

        // Queue an INSERT query to be run later
        $inserting = $this->queue->insert($obj, $this->records[$cls][$tempID]);

        // When the insert is run, update our record of the primary key
        $self = $this;
        $inserting->then(function(ResultSet $r) use ($self, $obj, $tempID) {
            $self->updatePrimaryKey($obj, $tempID, $r->lastInsertID());
        });

        // Kill the updated records for this object since this is the initial inset
        unset($this->updated[$cls][$tempID]);

        // Return the promise for the user to do fun things with
        return $inserting;
    }


    /**
     * Commit changed to the database. Will automatically call
     * {@link Session::flush()} to send queries to the database
     * then commit the transaction. You should almost always
     * call this instead of {@link Session::flush()}
     */
    public function commit() {
        $this->flush();
        $this->engine->commitTransaction();
    }


    /**
     * Returns a DDL object for this session
     *
     * @return DDL
     */
    public function ddl() {
        return new DDL($this);
    }


    /**
     * Return the Engine associated with this session
     *
     * @return IEngine
     */
    public function engine() {
        return $this->engine;
    }


    /**
     * Execute a query, wrap the results in the given class
     * and return a set.
     *
     * @param string $cls Class Name of a DataMapper subclass
     * @param Query $query Query to execute
     * @return array Set of Objects
     */
    public function execute($cls, $query) {
        $rows = $this->engine->query($query);
        return $this->wrap($cls, $rows);
    }


    /**
     * Start a new transaction (if one isn't already open), and flushes
     * all pending queries to the RDBMS in the order they were created.
     * Leaves the transaction open for you to commit / rollback.
     */
    public function flush() {
        $this->engine->beginTransaction();
        $this->queue->flush($this->engine);
    }


    /**
     * Get the primary key for the given class and record. If one
     * doesn't exist yet, it generates a transient key to be used
     * until the database allocates the object a real key.
     *
     * @param string $cls Class Name of a DataMapper subclass
     * @param array $record Data Record
     * @return array
     */
    protected function getPrimaryKey($cls, $record = array()) {
        $pk = array();
        foreach ($cls::schema()->getPrimaryKey()->listColumns() as $column) {
            $name = $column->getName();
            if (isset($record[$name])) {
                $pk[] = $record[$name];
            }
        }

        // Generate a single string from the (possibly composite) primary key
        $pk = implode("-", $pk);

        // PK probably hasn't been allocated by the DB yet. Create a temporary
        // one to identify it's record in the session
        if (empty($pk)) {
            $pk = "TRANSIENT-KEY-" . rand();
        }

        return $pk;
    }


    /**
     * Return a reference to the given field
     *
     * @param string $cls Class Name
     * @param mixed $id Record ID
     * @param string $prop Column Name
     * @return mixed
     */
    public function &getProperty($cls, $id, $prop) {
        return $this->records[$cls][$id][$prop];
    }


    /**
     * Get an object from the session store without running a query.
     *
     * @param string $cls DataMapper type
     * @param string $sid SessionID
     * @return DataMapper
     */
    public function object($cls, $sid) {
        if (is_array($sid)) {
            $sid = $this->getPrimaryKey($cls, $sid);
        }

        if (isset($this->records[$cls][$sid])) {
            return $cls::from_session($this, $sid);
        }
    }


    /**
     * Return a SessionSelect for the given class
     *
     * @return SessionSelect
     */
    public function objects($cls) {
        return new SessionSelect($this, $cls);
    }


    /**
     * Delete the given object form the database
     *
     * @return Promise resolved when DELETE statement is run
     */
    public function remove(DataMapper &$obj) {
        $cls = get_class($obj);

        $keys = array();
        foreach ($cls::schema()->getPrimaryKey()->listColumns() as $column) {
            $name = $column->getName();
            $keys[$name] = $obj->$name;
        }

        // Queue an INSERT query to be run later
        $deleting = $this->queue->delete($cls, $keys);

        // Delete records
        $id = $obj->getSessionID();
        unset($this->records[$cls][$id]);
        unset($this->updated[$cls][$id]);

        // Return the promise for the user to do fun things with
        return $deleting;
    }


    /**
     * Queue an UPDATE query to be run later to update values
     * set with {@see Session::setProperty()}
     *
     * @param string $cls Class Name
     * @param mixed $id Record ID
     * @return Promise Resolved when the query is run
     */
    public function save($cls, $id) {
        // If nothing is in $this->updated, theres nothing to do here
        if (empty($this->updated[$cls][$id])) {
            return;
        }

        // Filter the UPDATE by primary key
        $pk = array();
        foreach ($cls::schema()->getPrimaryKey()->listColumns() as $column) {
            $name = $column->getName();
            $value = $this->getProperty($cls, $id, $name);

            // Abort if primary key is partial
            if (is_null($value)) {
                throw new Exception("Can not send UPDATE for model when primary key is null");
            }

            $pk[$name] = $value;
        }

        // Schedule the UPDATE
        $updating = $this->queue->update($cls, $pk, $this->updated[$cls][$id]);

        // Remove these rows form $this->updated so we won't try to update
        // them again later
        unset($this->updated[$cls][$id]);

        return $updating;
    }


    /**
     * Update a property value
     *
     * @param string $cls Class Name
     * @param mixed $id Record ID
     * @param string $prop Column Name
     * @param mixed $value Property Value
     */
    public function setProperty($cls, $id, $prop, $value) {
        // Don't update the value if it's already equivalent
        if (isset($this->records[$cls][$id][$prop]) && $this->records[$cls][$id][$prop] === $value) {
            return;
        }

        $this->records[$cls][$id][$prop] = $value;
        $this->updated[$cls][$id][$prop] = $value;
    }


    /**
     * Accepts an object and moves it from one primary key to a new primary
     * key. This is only ever needed when migrating from a transient key
     * to a real db-allocated key after an INSERT.
     *
     * @param DataMapper $obj
     * @param mixed $oldID
     * @param mixed $newID
     */
    public function updatePrimaryKey(DataMapper $obj, $oldID, $newID) {
        $cls = get_class($obj);

        // Update the auto increment column in our rocred to match the new value
        foreach ($cls::schema()->getPrimaryKey()->listColumns() as $column) {
            $name = $column->getName();
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

        // Notify Object of it's relocation
        $obj->onPrimaryKeyAllocated();
    }


    /**
     * Wrap a set of rows in the given DataMapper class
     *
     * @param string $cls Class Name
     * @param array $rows Two-dimensional array of records
     */
    protected function wrap($cls, $rows) {
        $objects = array();
        $table = $cls::schema();
        $rows = $rows ?: array();

        foreach ($rows as $row) {
            $sid = $this->getPrimaryKey($cls, $row);

            $record = array();
            foreach ($row as $column => $value) {
                $record[$column] = $table->getColumn($column)->decode($value);
            }

            $this->records[$cls][$sid] = $record;
            $objects[] = $this->object($cls, $sid);
        }

        return $objects;
    }
}
