<?php

namespace  Alchemy\orm;


class OneToOne extends Relationship {

    /**
     * Return true if the origin of this relationship is the source of
     * foreign key index. False if the source of the foreign key is the
     * destination of this relationship
     *
     * @return bool
     */
    public function isParent() {
        return $this->origin === $this->getForeignKey()->getSourceTable();
    }


    public function getRemoteColumnMap($origin) {
        $map = $this->getForeignKey()->getNameMap();
        $map = $this->isParent() ? array_flip($map) : $map;

        foreach ($map as $remote => &$local) {
            $local = $origin->{$local};
        }

        return $map;
    }


    public function set($origin, $remote) {
        $this->assertDestinationType($remote);

        return $this->isParent()
            ? $origin->cascadeForeignKey($remote, $this)
            : $remote->cascadeForeignKey($origin, $this->getInverse());
    }
}