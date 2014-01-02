<?php

namespace Alchemy\engine;
use Alchemy\expression\IQuery;
use Alchemy\dialect\DialectTranslator;
use Alchemy\util\Monad;
use PDO;


class Engine implements IEngine {
    protected $connector;
    protected $dialect;
    protected $echoQueries = false;


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


    protected function echoQuery($sql) {
        if (!$this->echoQueries) {
            return;
        }

        if (is_callable($this->echoQueries)) {
            return $this->echoQueries($sql);
        }

        echo $sql . "\n";
    }


    public function query($query) {
        $sql = (string)$this->dialect->translate($query);
        $params = $query->getParameters();
        return $this->execute($sql, $params);
    }


    public function execute($sql, $params = array()) {
        $this->echoQuery($sql);
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


    public function setEcho($echoQueries) {
        $this->echoQueries = $echoQueries;
    }
}
