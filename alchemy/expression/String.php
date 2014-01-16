<?php

namespace Alchemy\expression;


/**
 * Represent a VARCHAR column in SQL
 */
class String extends Column {
    protected static $default_args = array(255,
        'collation' => null,
    );


    /**
     * Get max length of the string
     */
    public function getSize() {
        return $this->args[0];
    }
}
