<?php

namespace Alchemy\engine;
use PDOStatement;
use Iterator;


/**
 * Interface for result set implementations
 */
interface IResultSet extends Iterator {

    /**
     * Return the last inserted ID form the database
     *
     * @return integer
     */
    public function lastInsertID();


    /**
     * Return the row count of the result set
     *
     * @return integer
     */
    public function rowCount();
}
