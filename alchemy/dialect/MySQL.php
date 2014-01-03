<?php

namespace Alchemy\dialect;
use Exception;


class MySQL_Integer extends ANSI_Integer {
    public function definition() {
        $sql = parent::definition();
        $sql .= $this->getKwarg('auto_increment') ? " AUTO_INCREMENT" : "";
        return $sql;
    }
}
