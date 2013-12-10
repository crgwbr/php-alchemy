<?php

namespace Alchemy\orm\schema;


class Text extends String {

    protected static $default_kwargs = array(
        'collation' => null,
        'default' => null,
        'index' => false,
        'null' => false,
        'primary_key' => false,
        'unique' => false,
    );

    public function columnDefinition() {
        $def = array();
        $def[] = "`{$this->name}` TEXT";

        if ($this->kwargs['collation']) {
            $def[] = "COLLATE {$this->kwargs['collation']}";
        }

        if ($this->kwargs['null']) {
            $def[] = "NULL";
        } else {
            $def[] = "NOT NULL";
        }

        if (!is_null($this->kwargs['default'])) {
            $default = str_replace("'", "\\'", $this->kwargs['default']);
            $def[] = "DEFAULT '{$default}'";
        }

        return implode(" ", $def);
    }
}
