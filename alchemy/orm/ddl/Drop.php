<?php

namespace Alchemy\orm\ddl;
use Alchemy\engine\IEngine;


class Drop {
    private $mapper;

    public function __construct($mapper) {
        $this->mapper = $mapper;
    }


    public function execute(IEngine $engine) {
        $cls = $this->mapper;
        $table = $cls::table_name();
        $sql = "DROP TABLE IF EXISTS {$table};";
        $engine->execute($sql);
    }
}