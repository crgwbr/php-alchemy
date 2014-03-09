<?php

namespace  Alchemy\orm;
use Alchemy\core\schema\Table;
use Alchemy\core\query;
use Alchemy\util\promise\Promise;
use Alchemy\util\DataTypeLexer;
use Exception;
use ReflectionClass;


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
    protected static $schema_args = array();

    private static $schema_cache = array();

    private $deltas = array();
    private $dependancies = array();
    private $relatedSets = array();
    private $session;
    private $sessionID;
    private $persisting;


    /**
     * Instantiate a domain object from the session
     *
     * @param Session $session The database session which owns this record
     * @param mixed $sessionID A pointer to the record for this objects data in the session
     */
    public static function from_session(Session $session, $sessionID) {
        $cls = get_called_class();
        $obj = new $cls();
        $obj->setSession($session, $sessionID, true);
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
                $reflection = new ReflectionClass($cls);
                if (!$reflection->isAbstract()) {
                    $result[] = $cls;
                }
            }
        }

        return $result;
    }


    /**
     * Call immediately after data mapper definition
     */
    public static function register() {
        static::schema();
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
            $name = static::table_name();
            $args = static::$schema_args + array('class' => $cls);

            $tablefn = function() use ($name, $args) {
                return Table::ORM($name, $args);
            };

            self::$schema_cache[$cls] = new Promise($tablefn, "Alchemy\orm\ORMTable");
            Table::register_name($name, self::$schema_cache[$cls], true);
            self::$schema_cache[$cls]->register(true);
        }

        return self::$schema_cache[$cls];
    }


    /**
     * Return a table reference for this model
     *
     * @return ORMTableRef
     */
    public static function table() {
        return new ORMTableRef(static::schema());
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
        $this->persisting = new Promise();
        foreach (static::schema()->listRelationships() as $name => $r) {
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

        if (!static::schema()->hasColumn($prop)) {
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

        if (!static::schema()->hasColumn($prop)) {
            throw new Exception("Property [{$prop}] is not a configured column");
        }

        $this->deltas[$prop] = $value;
    }


    /**
     * Add an object to the list of objects that must be persisted
     * before this object can be persisted.
     *
     * @param DataMapper $obj
     */
    protected function addPersistanceDependancy(DataMapper $obj) {
        if ($this->isTransient()) {
            $this->dependancies[] = $obj;
        }
    }


    /**
     * Automatically apply properties of this object to another's foreign keys
     * according to a Relationship.
     *
     * @param  DataMapper $child object to affect
     * @param  Relationship $rel defines what properties to affect on the child
     * @return Promise           resolves when FK is cascaded
     */
    public function cascadeForeignKey($child, $rel) {
        $child->addPersistanceDependancy($this);

        return $this->persisting->then(function($self) use ($child, $rel) {
            $child->set($rel->getRemoteColumnMap($self));

            if ($child->getSession()) {
                $child->save(!$child->isTransient());
            }

            return $self;
        });
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
     * Return a promise resolved when all objects that this object references
     * by foreign key have been persisted
     *
     * @return Promise
     */
    public function onDependanciesPersisted() {
        $promises = array();
        foreach ($this->dependancies as $d) {
            $promises[] = $d->onPersisted();
        }

        return Promise::when($promises);
    }


    /**
     * Return a Promise for when this object is actually persisted,
     * if it is not already.
     *
     * @return Promise resolves to $this when this DataMapper is persisted
     */
    public function onPersisted() {
        return $this->persisting;
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
     * Set multiple properties on this object.
     *
     * @param array $map [Property => Value, ...]
     */
    public function set(array $map) {
        foreach ($map as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }


    /**
     * Set the Session and Session pointer for this object
     *
     * @param Session $session Session which owns this object
     * @param string $sessionID Pointer to data record in $session
     */
    public function setSession(Session $session, $sessionID, $persisted = false) {
        $this->session = $session;
        $this->sessionID = $sessionID;

        if ($persisted) {
            $this->persisting->resolve($this);
            $this->dependancies = array();
        }
    }
}
