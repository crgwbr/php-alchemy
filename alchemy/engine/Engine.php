<?php

namespace Alchemy\engine;
use Alchemy\expression\IQuery;
use Alchemy\dialect\DialectTranslator;
use Alchemy\util\Monad;
use PDO;


/**
 * Basic Engine implementation using PDO as it's DBAPI layer
 */
class Engine implements IEngine {
    protected $connector;
    protected $dialect;
    protected $echoQueries = false;
    protected $pendingTransaction = false;


    /**
     * Object constructor. Opens a connection to the database using PDO
     *
     * @param string $dsn See PDO documentation for DSN reference
     * @param string $username
     * @param string $password
     */
    public function __construct($dsn, $username = '', $password = '') {
        // Get connection
        $this->connector = new PDO($dsn, $username, $password);
        $this->connector->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Set dialect
        $dialect = explode(":", $dsn);
        $dialect = reset($dialect);
        $this->dialect = new DialectTranslator($dialect);
    }


    /**
     * @see IEngine::beginTransaction()
     */
    public function beginTransaction() {
        if (!$this->pendingTransaction) {
            $this->connector->beginTransaction();
        }
    }


    /**
     * @see IEngine::commitTransaction()
     */
    public function commitTransaction() {
        if ($this->pendingTransaction) {
            $this->connector->commit();
            $this->pendingTransaction = false;
        }
    }


    /**
     * Log a SQL statement if echo is enabled
     *
     * @param string $sql
     */
    protected function echoQuery($sql) {
        if (!$this->echoQueries) {
            return;
        }

        if (is_callable($this->echoQueries)) {
            return $this->echoQueries($sql);
        }

        echo $sql . "\n";
    }


    /**
     * @see IEngine::query()
     */
    public function query($query) {
        $sql = (string)$this->dialect->translate($query);
        $params = $query->getParameters();
        return $this->execute($sql, $params);
    }


    /**
     * @see IEngine::execute()
     */
    public function execute($sql, $params = array()) {
        $this->echoQuery($sql);
        $statement = $this->connector->prepare($sql);

        foreach ($params as $i => $param) {
            $statement->bindValue($i + 1, $param->getValue(), $param->getDataType());
        }

        $statement->execute();
        return new ResultSet($this->connector, $statement);
    }


    /**
     * @see IEngine::rollbackTransaction()
     */
    public function rollbackTransaction() {
        if ($this->pendingTransaction) {
            $this->connector->rollBack();
        }
    }


    /**
     * Optionally enable echo'ing of SQL run on the RDBMS.
     *
     * @param mixed $echoQueries False to disable. True to log to STDOUT. Callable to enable custom logging
     */
    public function setEcho($echoQueries) {
        $this->echoQueries = $echoQueries;
    }
}
