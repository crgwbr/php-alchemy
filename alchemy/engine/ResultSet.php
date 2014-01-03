<?php

namespace Alchemy\engine;
use PDO;
use PDOStatement;
use Iterator;
use Exception;
use PDOException;


/**
 * Implementation of IResultSet for PDO results
 */
class ResultSet implements IResultSet {
    protected $connector;
    protected $statement;
    protected $index = 0;
    protected $current;


    /**
     * Object constructor.
     *
     * @param PDO $connector
     * @param PDOStatement $statement
     */
    public function __construct(PDO $connector, PDOStatement $statement) {
        $this->connector = $connector;
        $this->statement = $statement;

        try {
            $this->fetch();
        } catch (PDOException $e) {}
    }


    /**
     * @see Iterator::current()
     */
    public function current() {
        return $this->current;
    }


    /**
     * Fetch the next result from the cursor
     */
    protected function fetch() {
        $this->current = $this->statement->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * @see IResultSet::lastInsertID()
     */
    public function lastInsertID() {
        return $this->connector->lastInsertId();
    }


    /**
     * @see Iterator::key()
     */
    public function key() {
        return $this->index;
    }


    /**
     * @see Iterator::next()
     */
    public function next() {
        $this->index++;
        $this->fetch();
    }


    /**
     * @see Iterator::rewind()
     */
    public function rewind() {} // this is a forward-only iterator


    /**
     * @see IResultSet::rowCount()
     */
    public function rowCount() {
        return $this->statement->rowCount();
    }


    /**
     * @see Iterator::valid()
     */
    public function valid() {
        return !empty($this->current);
    }
}
