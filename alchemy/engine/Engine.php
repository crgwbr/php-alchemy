<?php

namespace Alchemy\engine;
use Alchemy\expression\IQuery;
use Alchemy\dialect\DialectTranslator;
use PDO;


class Engine implements IEngine {
    protected $connector;
    protected $dialect;


    public function __construct($dsn, $username = '', $password = '') {
        // Get connection
        $this->connector = new PDO($dsn, $username, $password);
        $this->connector->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Set dialect
        $dialect = explode(":", $dsn);
        $dialect = reset($dialect);
        $this->dialect = new DialectTranslator($dialect);
    }


    public function beginTransaction() {
        $this->connector->beginTransaction();
    }


    public function commitTransaction() {
        $this->connector->commitTransaction();
    }


    public function query(IQuery $query) {
        $sql = (string)$this->dialect->translate($query);
        $params = $query->getParameters();
        return $this->execute($sql, $params);
    }


    public function execute($sql, $params = array()) {
        $statement = $this->connector->prepare($sql);

        foreach ($params as $i => $param) {
            $statement->bindValue($i + 1, $param->getValue(), $param->getDataType());
        }

        $statement->execute();
        return new ResultSet($statement);
    }


    public function rollbackTransaction() {
        $this->connector->rollbackTransaction();
    }
}
