<?php

namespace Alchemy\expression;


/**
 * Represent an Integer in SQL
 */
class Float extends Column {
    protected static $default_args = array(23,
        'unsigned' => false,
    );


    /**
     * Get the floating point precision
     *
     * @return integer
     */
    public function getPrecision() {
        return $this->args[0];
    }
}
