<?php

namespace Alchemy\dialect;
use Alchemy\expression as expr;

class MySQLCompiler extends ANSICompiler {

    public function Create_Integer($obj) {
        $sql = $this->Create_Column($obj, true);
        $sql .= $obj->getArg('auto_increment') ? " AUTO_INCREMENT" : "";

        return $sql;
    }
 }