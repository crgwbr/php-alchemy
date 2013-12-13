<?php

namespace  Alchemy\orm;


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


    public static function schema_definition() {
        $cls = get_called_class();
        if (!array_key_exists($cls, self::$schema_cache)) {
            $columns = array();
            foreach ($cls::$props as $name => $definition) {
                $type = new DataTypeLexer($definition);
                $columnClass = static::SCHEMA_NS . $type->getType();
                $args = $type->getArgs();
                $kwargs = $type->getKeywordArgs();
                $column = new $columnClass($name, $args, $kwargs);
                $columns[$name] = $column;
            }

            self::$schema_cache[$cls] = $columns;
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
        $columns = static::schema_definition();
        if (!array_key_exists($prop, $columns)) {
            throw new Exception("Property [{$prop}] is not a configured column");
        }

        $cls = get_called_class();
        return array_key_exists($prop, $this->deltas)
            ? $this->deltas[$prop]
            : $this->session->getProperty($cls, $this->sessionID, $prop);
    }


    public function __set($prop, $value) {
        $columns = static::schema_definition();
        if (!array_key_exists($prop, $columns)) {
            throw new Exception("Property [{$prop}] is not a configured column");
        }

        $this->deltas[$prop] = $value;
    }


    public function save() {
        if (!$this->session) {
            throw new Exception("Can not save this DataMapper because it is not associated with a session");
        }

        $cls = get_class($this);
        $columns = static::schema_definition();
        foreach ($this->deltas as $prop => $value) {
            $this->session->setProperty($cls, $this->sessionID, $prop, $value);
        }

        $this->deltas = array();
    }


    public function setSession(&$session, $sessionID) {
        $this->session = &$session;
        $this->sessionID = $sessionID;
    }
}
