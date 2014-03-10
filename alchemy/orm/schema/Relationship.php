<?php

namespace  Alchemy\orm;
use Alchemy\core\Element;
use Alchemy\core\schema\Column;
use Alchemy\core\schema\ForeignKey;
use Alchemy\core\schema\Table;
use Alchemy\util\promise\Promise;
use Exception;


/**
 * Base class for defining the relationships between models
 * Subclass this to create separate relationship types like
 * OneToOne, OneToMany, etc.
 */
class Relationship extends Element {
    protected $name;
    protected $origin;
    protected $destination;
    protected $foreignKey;
    protected $args;
    protected $inverse;


    /**
     * Object constructor for an abstract model relationship
     *
     * @param string $name Relationship Name
     * @param string $origin Originating Class
     * @param array $args array([0] => "DestinationClass", [backref] => "BackrefName")
     */
    public function __construct($type, $args, $origin, $name) {
        parent::__construct($type);

        $this->name = $name;
        $this->origin = $origin;

        $def = static::get_definition($this->type);
        $this->args = self::normalize_arg($args, $def['defaults']);

        $dest = $this->args[0];
        $inverse = $this->args['inverse'];

        if (!$dest) {
            throw new Exception("Must provide Relationship Destination");
        }

        // class name, table name, or ORMTable object
        $this->destination = is_string($dest)
            ? (class_exists($dest) ? $dest::schema() : Table::find($dest))
            : $dest;

        $this->inverse = is_string($inverse)
            ? $this->createInverse($this->destination, $inverse)
            : $inverse;
    }


    public function assertDestinationType($dest) {
        $type = $this->getDestinationClass();
        if (!($dest instanceof $type)) {
            throw new Exception(get_class($dest) . " is not {$type}");
        }
    }


    /**
     * Create an inverse relationship to match this one.
     *
     * @param string $name
     */
    protected function createInverse($dest, $name) {
        $args = array(
            $this->origin,
            'inverse' => $this,
            'key' => $this->getForeignKey(),
        );

        $type = $this->getTag('rel.inverse');
        $inverse = Relationship::$type($args, $dest, $name);
        $dest->addRelationship($name, $inverse);

        return $inverse;
    }


    /**
     * Find the foreign key that defines how to traverse this relationship
     *
     * @return ForeignKey
     */
    protected function findForeignKey() {
        // User might have specified the key in the definition
        if ($key = $this->args['key']) {
            return is_string($key)
                ? Column::find($key, $this->origin)->getForeignKey()
                : $key;
        }

        // Try and infer the foreign key
        $index = $this->findForeignKeyImpl($this->origin, $this->destination)
              ?: $this->findForeignKeyImpl($this->destination, $this->origin);

        if ($index) {
            return $index;
        }

        throw new Exception('ForeignKey could not be found');
    }


    /**
     * Implementation function used by {@see Relationship::findForeignKey()}
     *
     * @param Table $tableA
     * @param Table $tableB
     * @return ForeignKey
     */
    protected function findForeignKeyImpl($tableA, $tableB) {
        $fk = null;

        foreach ($tableA->listIndexes() as $index) {
            if ($index instanceof ForeignKey) {
                if ($index->getSourceTable()->getName() == $tableB->getName()) {
                    if ($fk) {
                        throw new Exception("ForeignKey selection is ambiguous for table[{$tableB->getName()}]");
                    }
                    $fk = $index;
                }
            }
        }

        return $fk;
    }


    /**
     * Get the name of the backref of this relationship
     *
     * @return string
     */
    public function getBackref() {
        return $this->inverse ? $this->inverse->getName() : '';
    }


    /**
     * Get the inverse side of this relationship
     *
     * @return string
     */
    public function getInverse() {
        return $this->inverse;
    }



    /**
     * Get the destination class name
     *
     * @return string
     */
    public function getDestinationClass() {
        return $this->destination->getClass();
    }


    /**
     * Get the name of this relationship
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }


    public function getRef($origin) {
        $expr = new Promise(null, "Alchemy\core\query\Predicate");
        $ref = new ORMTableRef($this->destination, $expr);
        $expr->resolve($ref->equal($this->getRemoteColumnMap($origin)));

        return $ref;
    }


    /**
     * Get the ForeignKey associated with this relationship
     *
     * @return ForeignKey
     */
    public function getForeignKey() {
        if (!$this->foreignKey) {
            $this->foreignKey = $this->findForeignKey();
        }
        return $this->foreignKey;
    }


    /**
     * Get the class name of the originating class
     *
     * @return string
     */
    public function getOriginClass() {
        return $this->origin->getClass();
    }


    /**
     * Return true if this relationship can structurally only
     * ever return a single object.
     *
     * @return bool
     */
    public function isSingleObject() {
        return !!$this->getTag('rel.single');
    }


    /**
     * Return true if the origin of this relationship is the source of
     * foreign key index. False if the source of the foreign key is the
     * destination of this relationship
     *
     * @return bool
     */
    public function isParent() {
        return !!$this->getTag('rel.parent');
    }


    /**
     * Return true if this relationship can potentially be NULL (empty).
     *
     * @return bool
     */
    public function isNullable() {
        return true;
    }


    /**
     * Return a map of remote column names and their values according
     * to this relationship, relative to $origin, for querying. Applies
     * whether $origin is a table reference or a DataMapper. Must be
     * implemented by all Relationship types.
     *
     * @param  mixed $origin
     * @return array         [Column => Value, ...]
     */
    public function getRemoteColumnMap($origin) {
        return array();
    }
}
