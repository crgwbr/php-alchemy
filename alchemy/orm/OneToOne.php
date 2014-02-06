<?php

namespace  Alchemy\orm;


class OneToOne extends Relationship {
    protected static $inverseType = 'OneToOne';


    /**
     * Object constructor for an one-to-one relationship
     *
     * @param string $name Relationship Name
     * @param string $origin Originating Class
     * @param array $args array([0] => "DestinationClass", [backref] => "BackrefName")
     * @param bool $createBackref Internal Use Only
     */
    public function __construct($name, $origin, $args, $createBackref = true) {
        parent::__construct($name, $origin, $args, $createBackref);

        if ($this->origin === $this->destination) {
            $this->isParent = $createBackref;
        }
    }


    public function hasSingleObjectConstraint() {
        return true;
    }
}