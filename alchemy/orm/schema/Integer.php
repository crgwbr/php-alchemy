<?php

namespace Alchemy\orm\schema;


class Integer extends Column {
    const DEFAULT_WIDTH = 11;

    protected static $default_args = array(11);
    protected static $default_kwargs = array(
        'auto_increment' => false,
        'default' => null,
        'index' => false,
        'null' => false,
        'primary_key' => false,
        'unique' => false,
        'unsigned' => false,
    );


    public function columnDefinition() {
        $def = array();
        $width = $this->args[0];
        $def[] = "{$this->name} INT({$width})";

        if ($this->kwargs['unsigned']) {
            $def[] = "unsigned";
        }

        if ($this->kwargs['null']) {
            $def[] = "NULL";
        } else {
            $def[] = "NOT NULL";
        }

        if ($this->kwargs['auto_increment']) {
            $def[] = "AUTO_INCREMENT";
        } elseif (!is_null($this->kwargs['default'])) {
            $def[] = "DEFAULT {$this->kwargs['default']}";
        }

        return implode(" ", $def);
    }


    public function decode($value) {
        return (int)$value;
    }


    public function encode($value) {
        return new \Alchemy\expression\Integer((int)$value);
    }
}
