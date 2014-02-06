<?php

namespace  Alchemy\orm;
use Alchemy\core\schema\Table;
use Alchemy\core\query;
use Alchemy\util\promise\Promise;
use Alchemy\util\DataTypeLexer;
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
    protected static $relationships = array();

    private static $schema_cache = array();
    private static $relationship_cache = array();

    private $deltas = array();
    private $relatedSets = array();
    private $session;
    private $sessionID;

    private $onPrimaryKeyAlloc = array();


    /**
     * Add a relationship to this model
     *
     * @param string $name
     * @param string or Relationship $def
     */
    public static function add_relationship($name, $def) {
        $cls = get_called_class();
        if (!array_key_exists($cls, self::$relationship_cache)) {
            self::$relationship_cache[$cls] = array();
        }

        // Already Exists?
        if (array_key_exists($name, self::$relationship_cache[$cls])) {
            return;
        }

        if (is_string($def)) {
            $l = new DataTypeLexer($def);
            $type = __NAMESPACE__ . '\\' . $l->getType();
            $args = $l->getArgs();
            $r = new $type($name, $cls, $args);
        } else {
            $r = $def;
        }

        self::$relationship_cache[$cls][$name] = $r;
    }


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
     * List all subclasses of DataMapper. Only works for
     * classes already loaded by PHP.
     *
     * @return array
     */
    public static function list_mappers() {
        $result = array();
        foreach (get_declared_classes() as $cls) {
            if (is_subclass_of($cls, __CLASS__)) {
                $result[] = $cls;
            }
        }

        return $result;
    }


    /**
     * List the relationships belonging to this model
     *
     * @return array
     */
    public static function list_relationships() {
        $cls = get_called_class();
        if (!array_key_exists($cls, self::$relationship_cache)) {
            self::$relationship_cache[$cls] = array();
        }

        foreach (static::$relationships as $name => $def) {
            static::add_relationship($name, $def);
        }

        return self::$relationship_cache[$cls];
    }


    /**
     * Register the model's table and relationships. This should be called
     * immediately after defining a new model class.
     */
    public static function register() {
        static::schema(); // Table Registration
        static::list_relationships(); // Relationship Registration
    }


    /**
     * Gen an instance of Table that represents the schema of this
     * domain object.
     *
     * @return Table
     */
    public static function schema() {
        $cls = get_called_class();

        if (!array_key_exists($cls, self::$schema_cache)) {
            $name    = $cls::table_name();
            $props   = $cls::$props;
            $indexes = $cls::$indexes;

            $tablefn = function() use ($name, $props, $indexes) {
                return new Table($name, $props, $indexes);
            };

            self::$schema_cache[$cls] = new Promise($tablefn, "Alchemy\core\schema\Table");
            self::$schema_cache[$cls]->register();
        }

        return self::$schema_cache[$cls];
    }


    /**
     * Return a table reference for this model
     *
     * @return query\TableRef
     */
    public static function table() {
        return new query\TableRef(static::schema());
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
     * Object constructor.
     */
    public function __construct() {
        foreach (static::list_relationships() as $name => $r) {
            $this->relatedSets[$name] = new RelatedSet($this, $r);
        }
    }


    /**
     * Get the value of a column on this object
     *
     * @param string $prop
     * @return mixed
     */
    public function __get($prop) {
        if (array_key_exists($prop, $this->relatedSets)) {
            $set = $this->getRelatedSet($prop);
            return $set->isSingleObject() ? $set->first() : $set;
        }

        $table = static::schema();
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
        if (array_key_exists($prop, $this->relatedSets)) {
            $this->relatedSets[$prop]->set($value);
            return;
        }

        $table = static::schema();
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
        foreach (static::schema()->getPrimaryKey()->listColumns() as $column) {
            $pk[] = $this->{$column->getName()};
        }

        return $pk;
    }


    /**
     * Return a related set object by relationship name
     */
    public function getRelatedSet($name) {
        return $this->relatedSets[$name];
    }


    /**
     * Return the Session of this object
     *
     * @return Session
     */
    public function getSession() {
        return $this->session;
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
     * Return true if this model doesn't yet exist in the database
     *
     * @return bool
     */
    public function isTransient() {
        foreach ($this->getPrimaryKey() as $value) {
            if (is_null($value)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Event invoked by Session when this model is migrated from using a
     * temporary in-memory key to a real (saved in the RDBMS) primary key.
     */
    public function onPrimaryKeyAllocated($fn = null) {
        // Register callback?
        if ($fn) {
            $this->onPrimaryKeyAlloc[] = $fn;
            return;
        }

        // Trigger callbacks
        foreach ($this->onPrimaryKeyAlloc as $fn) {
            $fn($this);
        }
    }


    /**
     * Revert all unsaved changed to this model
     */
    public function rollback() {
        $this->deltas = array();
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
