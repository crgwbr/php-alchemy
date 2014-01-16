<?php

namespace Alchemy\expression;


/**
 * Represent an BigInt in SQL
 */
class BigInt extends Integer {
    protected static $default_args = array(20);
}
