<?php

namespace Alchemy\expression;


/**
 * Represent a BINARY column in SQL
 */
class Binary extends Column {
    protected static $default_args = array(255);


    /**
     * Get max length of the string
     */
    public function getSize() {
        return $this->args[0];
    }
}
