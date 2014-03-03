<?php

namespace  Alchemy\orm;


/**
 * Defines a OneToMany relationship. This is the parent side of a
 * standard foreign key system.
 */
class OneToMany extends Relationship {

    public function getRemoteColumnMap($origin) {
        $map = array_flip($this->getForeignKey()->getNameMap());

        foreach ($map as $remote => &$local) {
            $local = $origin->{$local};
        }

        return $map;
    }


    public function add($origin, $child) {
        $this->assertDestinationType($child);

        return $origin->cascadeForeignKey($child, $this);
    }
}
