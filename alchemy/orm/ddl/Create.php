<?php

namespace Alchemy\orm\ddl;
use Alchemy\engine\Engine;


class Create {
    private $mapper;

    public function __construct($mapper) {
        $this->mapper = $mapper;
    }


    public function execute(Engine $engine) {
        $columns = $this->listColumns();
        $columns = implode(", ", $columns);
        $cls = $this->mapper;
        $table = $cls::table_name();
        $sql = "CREATE TABLE IF NOT EXISTS {$table} ({$columns});";
        $engine->execute($sql);
    }


    protected function listColumns() {
        $columns = array();
        $cls = $this->mapper;

        foreach ($cls::schema_definition() as $name => $column) {
            $def = $column->columnDefinition();
            if ($def) {
                $columns[] = $def;
            }
        }

        return $columns;
    }
}