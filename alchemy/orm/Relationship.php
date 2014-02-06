<?php

namespace  Alchemy\orm;
use Alchemy\core\schema\ForeignKey;
use Alchemy\core\schema\Column;
use Exception;


/**
 * Base class for defining the relationships between models
 * Subclass this to create separate relationship types like
 * OneToOne, OneToMany, etc.
 */
abstract class Relationship {
    protected static $inverseType = null;
    protected $name;
    protected $origin;
    protected $destination;
    protected $backref;
    protected $isParent;
    protected $foreignKey;


    /**
     * Object constructor for an abstract model relationship
     *
     * @param string $name Relationship Name
     * @param string $origin Originating Class
     * @param array $args array([0] => "DestinationClass", [backref] => "BackrefName")
     * @param bool $createBackref Internal Use Only
     */
    public function __construct($name, $origin, $args, $createBackref = true) {
        $this->name = $name;
        $this->origin = $origin;

        if (!isset($args[0])) {
            throw new Exception("Must provide Relationship Destination");
        }
        $this->destination = $args[0];

        // Find the ForeignKey that structually represents this relationship
        $this->foreignKey = $this->findForeignKey($args);

        // Assemble the inverse side of this relationship
        $this->backref = array_key_exists('backref', $args)
           ? $args['backref']
           : "AutoBackref_{$origin}_{$name}";

        if ($createBackref) {
            $this->createBackref($this->backref);
        }
    }


    /**
     * Create an inverse relationship to match this one.
     *
     * @param string $name
     */
    protected function createBackref($name) {
        $type = __NAMESPACE__ . "\\" . static::$inverseType;
        $args = array(
            $this->origin,
            'backref' => $this->name,
            'key' => $this->foreignKey
        );

        $r = new $type($name, $this->destination, $args, false);

        $dest = $this->destination;
        $dest::add_relationship($name, $r);
    }


    /**
     * Find the foreign key that defines how to traverse this relationship
     *
     * @return ForeignKey
     */
    protected function findForeignKey($args) {
        // User might have specified the key in the definition
        if (array_key_exists('key', $args)) {
            $origin = $this->origin;
            $origin = $origin::schema();

            if (is_string($args['key'])) {
                $fk = Column::find($args['key'], $origin)->getForeignKey();
            } else {
                $fk = $args['key'];
            }

            $this->isParent = ($origin->getName() === $fk->getTable()->getName());
            return $fk;
        }

        // Try and infer the foreign key
        $origin = $this->origin;
        $origin = $origin::schema();
        $destination = $this->destination;
        $destination = $destination::schema();

        $index = $this->findForeignKeyImpl($origin, $destination);
        if ($index) {
            $this->isParent = ($this->origin === $this->destination);
            return $index;
        }

        $index = $this->findForeignKeyImpl($destination, $origin);
        if ($index) {
            $this->isParent = true;
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
        return $this->backref;
    }


    /**
     * Get an array of local/remote columns that map to each other
     * to form this relationship
     *
     * @return array array(array(LocaleColumn, RemoteColumn), ...)
     */
    public function getColumnPairings() {
        $key = $this->getForeignKey();

        if ($this->isParent()) {
            list($local, $remote) = array($key->listSources(), $key->listColumns());
        } else {
            list($remote, $local) = array($key->listSources(), $key->listColumns());
        }

        return array_map(function(Column $a, Column $b) {
            return array($a, $b);
        }, $local, $remote);
    }


    /**
     * Get the destination class name
     *
     * @return string
     */
    public function getDestination() {
        return $this->destination;
    }


    /**
     * Get the name of this relationship
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }


    /**
     * Get the ForeignKey associated with this relationship
     *
     * @return ForeignKey
     */
    public function getForeignKey() {
        return $this->foreignKey;
    }


    /**
     * Get the class name of the originating class
     *
     * @return string
     */
    public function getOrigin() {
        return $this->origin;
    }


    /**
     * Return true if this relationship can structurally only
     * ever return a single object.
     *
     * @return bool
     */
    abstract public function hasSingleObjectConstraint();


    /**
     * Return true if the origin of this relationship is the source of
     * foreign key index. False if the source of the foreign key is the
     * destination of this relationship
     *
     * @return bool
     */
    public function isParent() {
        return $this->isParent;
    }
}
