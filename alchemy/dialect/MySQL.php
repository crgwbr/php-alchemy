<?php

namespace Alchemy\dialect;
use Exception;


/**
 * Custom MySQL vernacular for Integer columns
 */
class MySQL_Integer extends ANSI_Integer {

    /**
     * Column Definition for a create table statement
     *
     * @return string
     */
    public function definition() {
        $sql = parent::definition();
        $sql .= $this->getKwarg('auto_increment') ? " AUTO_INCREMENT" : "";
        return $sql;
    }
}
