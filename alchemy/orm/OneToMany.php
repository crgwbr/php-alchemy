<?php

namespace  Alchemy\orm;


/**
 * Defines a OneToMany relationship. This is the parent side of a
 * standard foreign key system.
 */
class OneToMany extends Relationship {
    protected static $inverseType = 'ManyToOne';


    /**
     * This relationship is never constrained to one child object
     *
     * @return bool
     */
    public function hasSingleObjectConstraint() {
        return false;
    }


    /**
     * This relationship is always the parent
     *
     * @return bool
     */
    public function isParent() {
        return true;
    }
}
