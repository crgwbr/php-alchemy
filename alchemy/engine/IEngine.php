<?php

namespace Alchemy\engine;
use Alchemy\expression\IQuery;


/**
 * All Engine implementations should implement this interface
 */
interface IEngine {

    /**
     * Start an atomic transaction on the database. These should
     * generally not be held open very long in order to prevent
     * deadlocks
     */
    public function beginTransaction();


    /**
     * Commit a transaction as complete
     */
    public function commitTransaction();


    /**
     * Compile and run a SQL expression on the database
     *
     * @param Query|Monad Query to compile
     * @return ResultSet
     */
    public function query($query);


    /**
     * Execute raw SQL on the database connection
     *
     * @param string $sql Statement string
     * @param array $params Params to bind to statement
     * @return ResultSet
     */
    public function execute($sql, $params = array());


    /**
     * Revert a pending transaction on the database
     */
    public function rollbackTransaction();
}
