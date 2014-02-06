<?php

namespace Alchemy\orm;
use Alchemy\core\schema\Column;
use Alchemy\core\query\ColumnRef;
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
        $session = $this->getSession();
        $query = $this->buildSessionSelect($session);
        return call_user_func_array(array($query, $name), $args);
    }


    /**
     * Add an object to this relationship
     *
     * @param DataMapper $obj
     */
    public function add(DataMapper $obj) {
        if ($this->relationship->hasSingleObjectConstraint()) {
            throw new Exception(get_class($this->relationship) . " doesn't support add method");
        }

        $this->setForeignKeyValue($obj);
    }


    /**
     * Build a select with the where pre-seeded to match objects in this relationship
     *
     * @return SessionSelect
     */
    protected function buildSessionSelect($session) {
        $query = new SessionSelect($session, $this->relationship->getDestination());
        return $query->where($this->buildWhereCondition());
    }


    /**
     * Build and return a filter predicate for object that match this relationship
     *
     * @return Alchemy\core\query\Predicate
     */
    protected function buildWhereCondition() {
        $columns = $this->relationship->getColumnPairings();

        $owner = $this->owner;
        $filter = array_reduce($columns, function($filter, $columns) use ($owner) {
            list($local, $remote) = $columns;
            $local = $local->getRef();
            $remote = $remote->getRef();

            $local = $owner->{$local->name()};
            $condition = $remote->equal($local);
            if ($filter) {
                return $filter->and($c);
            }

            return $condition;
        });

        return $filter;
    }


    /**
     * Return the sorted parent/child object pair for the given object
     *
     * @param DataMapper $obj
     * @return array array(Parent, Child)
     */
    protected function getParentChild(DataMapper $obj) {
        if ($this->relationship->isParent()) {
            $parent = $this->owner;
            $child = $obj;
        } else {
            $parent = $obj;
            $child = $this->owner;
        }

        return array($parent, $child);
    }


    /**
     * Get the session associated with the owner of this set. Throws
     * an exception if no session exists
     *
     * @return Session
     */
    protected function getSession() {
        $session = $this->owner->getSession();
        if (!$session) {
            throw new Exception("Object must belong to session for relationship queries");
        }

        return $session;
    }


    /**
     * Return true if this set provably contains no more than a single object
     *
     * @return bool
     */
    public function isSingleObject() {
        return $this->relationship->hasSingleObjectConstraint();
    }


    /**
     * Get the first object in this relationship. If the relationship can
     * one possibly have one member, try and fetch it from memory rather than
     * running a query.
     *
     * @return DataMapper
     */
    public function first() {
        $session = $this->getSession();

        // Try to get object from memory session
        if ($this->relationship->hasSingleObjectConstraint()) {
            $pk = array();
            foreach ($this->relationship->getColumnPairings() as $pair) {
                list($local, $remote) = $pair;
                $pk[$remote->getName()] = $this->owner->{$local->getName()};
            }

            $cls = $this->relationship->getDestination();
            $obj = $session->object($cls, $pk);

            if ($obj) {
                return $obj;
            }
        }

        // Fallback to a query
        $query = $this->buildSessionSelect($session);
        return $query->first();
    }


    /**
     * Set the object for this relationship
     *
     * @param DataMapper $obj
     */
    public function set(DataMapper $obj) {
        if (!$this->relationship->hasSingleObjectConstraint()) {
            throw new Exception(get_class($this->relationship) . " doesn't support set method");
        }

        $this->setForeignKeyValue($obj);
    }


    /**
     * Links the given $obj to $this->owner via setting the foreign
     * key value equal to the source value.
     *
     * @param DataMapper $obj
     */
    protected function setForeignKeyValue($obj) {
        $type = $this->relationship->getDestination();
        if (!($obj instanceof $type)) {
            throw new Exception(get_class($obj) . " is not {$type}");
        }

        list($parent, $child) = $this->getParentChild($obj);

        // Set relationship columns for persistence
        $relationship = $this->relationship;
        $setFKValues = function($parent) use (&$relationship, &$child) {
            if ($parent->isTransient()) {
                return;
            }

            $fk = $relationship->getForeignKey();
            $childColumns = $fk->listColumns();
            $parentColumns = $fk->listSources();
            while (count($childColumns) > 0) {
                $childColumn = array_pop($childColumns);
                $childColumn = $childColumn->getName();
                $parentColumn = array_pop($parentColumns);
                $parentColumn = $parentColumn->getName();
                $child->$childColumn = $parent->$parentColumn;
            }

            $parentSession = $parent->getSession();
            $childSession = $child->getSession();
            if (!$childSession && $parentSession) {
                $parentSession->add($child);
            } else if ($childSession) {
                $child->save();
            }
        };

        // Update the foreign key columns both now, and whenever the parent's primary key changes
        $parent->onPrimaryKeyAllocated($setFKValues);
        $setFKValues($parent);
    }
}
