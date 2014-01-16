<?php

namespace Alchemy\expression;


/**
 * Represent an Integer in SQL
 */
class Float extends Column {
    protected static $default_args = array(23,
        'unsigned' => false,
    );


    public function getPrecision() {
        return $this->args[0];
    }
}
