<?php

namespace Alchemy\orm\schema;


class String extends Column {

    protected static $default_args = array(255);
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
        $size = $this->args[0];
        $def[] = "{$this->name} VARCHAR({$size})";

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


    public function decode($value) {
        return (string)$value;
    }


    public function encode($value) {
        return new \Alchemy\expression\String((string)$value);
    }
}
