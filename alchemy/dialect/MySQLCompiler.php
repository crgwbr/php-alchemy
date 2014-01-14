<?php

namespace Alchemy\dialect;
use Alchemy\expression as expr;

class MySQLCompiler extends ANSICompiler {

    public function Create_Integer(expr\Integer $obj) {
        $sql = parent::Create_Integer($obj);
        $sql .= $obj->isAutoIncremented() ? "AUTO_INCREMENT" : "";

        return $sql;
    }
 }