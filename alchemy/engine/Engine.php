<?php

namespace Alchemy\engine;
use Alchemy\expression\QueryManager;
use PDO;


class Engine {
    protected $connector;

    public function __construct($dsn, $username = null, $password = null) {
        $this->connector = new PDO($dsn, $username, $password);
        $this->connector->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


    public function commitTransaction() {
        $this->connector->commitTransaction();
    }


    public function query(QueryManager $query) {
        $statement = $this->connector->prepare((string)$query);

        foreach ($query->getParameters() as $i => $param) {
            $statement->bindValue($i + 1, $param->getValue(), $param->getDataType());
        }

        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        return $statement->fetchAll();
    }


    public function execute($sql) {
        $statement = $this->connector->prepare($sql);
        $statement->execute();
        return $statement->rowCount();
    }


    public function rollbackTransaction() {
        $this->connector->rollbackTransaction();
    }


    public function startTransaction() {
        $this->connector->beginTransaction();
    }
}
