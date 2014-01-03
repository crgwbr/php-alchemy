<?php

namespace Alchemy\engine;
use PDO;
use PDOStatement;
use Iterator;
use Exception;
use PDOException;


class ResultSet implements IResultSet {
    protected $connector;
    protected $statement;
    protected $index = 0;
    protected $current;


    public function __construct(PDO $connector, PDOStatement $statement) {
        $this->connector = $connector;
        $this->statement = $statement;

        try {
            $this->fetch();
        } catch (PDOException $e) {}
    }


    public function current() {
        return $this->current;
    }


    protected function fetch() {
        $this->current = $this->statement->fetch(PDO::FETCH_ASSOC);
    }


    public function lastInsertID() {
        return $this->connector->lastInsertId();
    }


    public function key() {
        return $this->index;
    }


    public function next() {
        $this->index++;
        $this->fetch();
    }


    public function rewind() {} // this is a forward-only iterator


    public function rowCount() {
        return $this->statement->rowCount();
    }


    public function valid() {
        return !empty($this->current);
    }
}
