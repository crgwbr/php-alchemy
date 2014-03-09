<?php

namespace  Alchemy\orm;


/**
 * This is the child side of the standard foreign key system
 */
class ManyToOne extends Relationship {

    public function getRemoteColumnMap($origin) {
        $map = $this->getForeignKey()->getNameMap();

        foreach ($map as $remote => &$local) {
            $local = $origin->{$local};
        }

        return $map;
    }


    public function set($origin, $parent) {
        $this->assertDestinationType($parent);

        return $parent->cascadeForeignKey($origin, $this->getInverse());
    }
}
