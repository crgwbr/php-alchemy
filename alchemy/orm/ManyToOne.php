<?php

namespace  Alchemy\orm;


/**
 * This is the child side of the standard foreign key system
 */
class ManyToOne extends Relationship {
    protected static $inverseType = 'OneToMany';


    /**
     * This relationship is always limited to exactly 0 or 1 parents
     *
     * @return bool
     */
    public function hasSingleObjectConstraint() {
        return true;
    }


    /**
     * This relationship is never the parent
     *
     * @return bool
     */
    public function isParent() {
        return false;
    }
}