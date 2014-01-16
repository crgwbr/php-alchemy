<?php

namespace  Alchemy\orm;
use Alchemy\expression\Table;
use Exception;


/**
 * Abstract base class for custom ORM models. this class
 * doesn't actually store any data, it merely acts as an
 * interface between Domain objects and the Session (which
 * actually owns all of the DB records).
 */
abstract class DataMapper {

    /**
     * Optional: Define the table name here. If left null, the
     * class name will be used
     */
    protected static $table_name = null;
    protected static $props = array();
    protected static $indexes = array();

    private static $schema_cache = array();
    private $deltas = array();
    private $session;
    private $sessionID;


    /**
     * Instantiate a domain object from the session
     *
     * @param Session $session The database session which owns this record
     * @param mixed $sessionID A pointer to the record for this objects data in the session
     */
    public static function from_session(Session $session, $sessionID) {
        $cls = get_called_class();
        $obj = new $cls();
        $obj->setSession($session, $sessionID);
        return $obj;
    }


    /**
     * Gen an instance of Table that represents the schema of this
     * domain object.
     *
     * @return Table
     */
    public static function table() {
        $cls = get_called_class();
        if (!array_key_exists($cls, self::$schema_cache)) {
            $table = new Table($cls::table_name(), $cls::$props, $cls::$indexes);
            self::$schema_cache[$cls] = $table;
        }

        return self::$schema_cache[$cls];
    }


    /**
     * Get the table name for this mapper
     *
     * @return string Table Name
     */
    public static function table_name() {
        $cls = static::$table_name ?: get_called_class();
        $cls = preg_replace("/[^A-Za-z0-9]/", "_", $cls);
        return $cls;
    }


    /**
     * Get the value of a column on this object
     *
     * @param string $prop
     * @return mixed
     */
    public function __get($prop) {
        $table = static::table();
        if (!$table->isColumn($prop)) {
            throw new Exception("Property [{$prop}] is not a configured column");
        }

        $cls = get_called_class();
        if (array_key_exists($prop, $this->deltas)) {
            return $this->deltas[$prop];
        }

        if (is_object($this->session)) {
            return $this->session->getProperty($cls, $this->sessionID, $prop);
        }

        return null;
    }


    /**
     * Get the value of a column on this object. Nothing is actually written
     * back to the session until {@DataMapper::save()} is called.
     *
     * @param string $prop Column Name
     * @param mixed $value
     */
    public function __set($prop, $value) {
        $table = static::table();
        if (!$table->isColumn($prop)) {
            throw new Exception("Property [{$prop}] is not a configured column");
        }

        $this->deltas[$prop] = $value;
    }


    /**
     * Return a set of values which represent this object's
     * primary key.
     *
     * @return array
     */
    public function getPrimaryKey() {
        $pk = array();
        foreach (static::table()->listPrimaryKeyComponents() as $name => $column) {
            $pk[] = $this->$name;
        }

        return $pk;
    }


    /**
     * Return the ID used to identify this objects record in the Session
     *
     * @return string
     */
    public function getSessionID() {
        return $this->sessionID;
    }


    /**
     * Save this object's data back to the session. By default this will queue
     * an UPDATE statement to be run on the server as soon as {@Session::commit()}
     * is called.
     *
     * @param bool $queueUpdate Default's to true
     */
    public function save($queueUpdate = true) {
        if (!$this->session) {
            throw new Exception("Can not save this DataMapper because it is not associated with a session");
        }

        $cls = get_class($this);
        foreach ($this->deltas as $prop => $value) {
            $this->session->setProperty($cls, $this->sessionID, $prop, $value);
        }

        if ($queueUpdate) {
            $this->session->save($cls, $this->sessionID);
        }

        $this->deltas = array();
    }


    /**
     * Set the Session and Session pointer for this object
     *
     * @param Session $session Session which owns this object
     * @param string $sessionID Pointer to data record in $session
     */
    public function setSession(Session $session, $sessionID) {
        $this->session = $session;
        $this->sessionID = $sessionID;
    }
}
