<?php

namespace  Alchemy\orm;


class OneToOne extends Relationship {

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
