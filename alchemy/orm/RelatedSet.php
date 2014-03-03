<?php

namespace Alchemy\orm;
use Alchemy\core\schema\Column;
use Alchemy\core\query\ColumnRef;
use Alchemy\core\query\TableRef;
use Exception;


/**
 * This class is used to manage the set of objects defined by a specific
 * owner object and one of it's relationships.
 */
class RelatedSet {
    private $owner;
    private $relationship;


    /**
     * Object constructor.
     *
     * @param DataMapper $owner
     * @param Relationship $relationship
     */
    public function __construct(DataMapper $owner, Relationship $relationship) {
        $this->owner = $owner;
        $this->relationship = $relationship;
    }


    /**
     * Allows SessionSelect querying with a predefined filter for this relationship
     */
    public function __call($name, $args) {
        if (method_exists($this->relationship, $name)) {
            array_unshift($args, $this->owner);
            return call_user_func_array(array($this->relationship, $name), $args);
        }

        throw new \Exception("Relationship::{$this->getType()} does not have a method {$name}()");
    }


    public function select() {
        if (!($session = $this->owner->getSession())) {
            throw new \Exception("Object must belong to session for relationship queries");
        }

        $query = new SessionSelect($session, $this->getDestinationClass());
        $map = $this->relationship->getRemoteColumnMap($this->owner);
        return $query->where($query->table()->equal($map));
    }


    public function all() {
        return $this->select()->all();
    }


    public function one() {
        return $this->select()->one();
    }


    public function first() {
        if (!($session = $this->owner->getSession())) {
            throw new Exception("Object must belong to session for relationship queries");
        }

        // Try to get object from memory session
        if ($this->relationship->isSingleObject()) {
            $map = $this->relationship->getRemoteColumnMap($this->owner);
            $obj = $session->object($this->getDestinationClass(), $map);

            if ($obj) {
                return $obj;
            }
        }

        // Fallback to a query
        return $this->select()->first();
    }
}
