<?php

namespace Alchemy\dialect;
use Alchemy\expression as expr;

class SQLiteCompiler extends ANSICompiler {

    public function Create_Integer(expr\Integer $obj) {
        $sql = "INTEGER";

        if ($obj->isPrimaryKey()) {
            $sql .= " PRIMARY KEY";
            if ($obj->isAutoIncremented()) {
                $sql .= " AUTOINCREMENT";
            }
        }

        return $sql;
    }


    public function Update(expr\Update $obj) {
        $this->pushConfig(array('alias_tables' => false));
        $sql = parent::Update($obj);
        $this->popConfig();

        return $sql;
    }
}