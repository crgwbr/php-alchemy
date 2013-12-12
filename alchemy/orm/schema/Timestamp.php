<?php

namespace Alchemy\orm\schema;


class Timestamp extends String {

    protected static $default_args = array(255);
    protected static $default_kwargs = array(
        'null' => false,
        'index' => false,
        'null' => false,
        'primary_key' => false,
        'unique' => false,
    );


    public function columnDefinition() {
        $def = array();
        $def[] = "{$this->name} TIMESTAMP";

        if ($this->kwargs['null']) {
            $def[] = "NULL";
        } else {
            $def[] = "NOT NULL";
        }

        return implode(" ", $def);
    }


    public function decode($value) {
        return new \Datetime($value);
    }


    public function encode($value) {
        if (!($value instanceof \Datetime)) {
            $value = $this->decode($value);
        }

        $value = $value->format('Y-m-d H:i:s');
        return parent::encode($value);
    }
}
