<?php

namespace Alchemy\dialect;
use Exception;


/**
 * Custom MySQL vernacular for Integer columns
 */
class MySQL_Integer extends ANSI_Integer {

    /**
     * @see ANSI_Integer::definition()
     */
    public function definition() {
        $sql = parent::definition();
        $sql .= $this->getKwarg('auto_increment') ? " AUTO_INCREMENT" : "";
        return $sql;
    }
}
