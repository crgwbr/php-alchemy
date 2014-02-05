<?php

namespace Alchemy\dialect;

class MySQLCompiler extends ANSICompiler {

    public function Create_Integer($obj) {
        $format = static::get_schema_format($obj->getType());
        $sql = $this->format($format,
            array($obj->getArg(0), $obj->getArg(1)));

        $sql .= $obj->getArg('auto_increment') ? " AUTO_INCREMENT" : "";

        return $sql;
    }
 }