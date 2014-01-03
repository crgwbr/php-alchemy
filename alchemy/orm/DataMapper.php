<?php

namespace  Alchemy\orm;
use Alchemy\expression\Table;
use Exception;


class DataMapper {
    const SCHEMA_NS = "Alchemy\\orm\\schema\\";

    protected static $table_name = null;

    private static $schema_cache = array();
    private $deltas = array();
    private $session;
    private $sessionID;


    public static function table_name() {
        $cls = static::$table_name ?: get_called_class();
        $cls = preg_replace("/[^A-Za-z0-9]/", "_", $cls);
        return $cls;
    }


    public static function table() {
        $cls = get_called_class();
        if (!array_key_exists($cls, self::$schema_cache)) {
            $table = new Table($cls::table_name(), $cls::$props);
            self::$schema_cache[$cls] = $table;
        }

        return self::$schema_cache[$cls];
    }


    public static function from_session(&$session, $sessionID) {
        $cls = get_called_class();
        $obj = new $cls();
        $obj->setSession($session, $sessionID);
        return $obj;
    }


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


    public function __set($prop, $value) {
        $table = static::table();
        if (!$table->isColumn($prop)) {
            throw new Exception("Property [{$prop}] is not a configured column");
        }

        $this->deltas[$prop] = $value;
    }


    public function getPrimaryKey() {
        $pk = array();
        foreach (static::table()->listPrimaryKeyComponents() as $name => $column) {
            $pk[] = $this->$name;
        }

        return $pk;
    }


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


    public function setSession($session, $sessionID) {
        $this->session = $session;
        $this->sessionID = $sessionID;
    }
}
