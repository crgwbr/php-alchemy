<?php

namespace Alchemy\engine;
use Alchemy\expression\IQuery;
use Alchemy\dialect\Compiler;
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
    public function __construct(Compiler $dialect, $dsn, $username = '', $password = '') {
        // Get connection
        $this->connector = new PDO($dsn, $username, $password);
        $this->connector->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->dialect = $dialect;
    }


    /**
     * Start an atomic transaction on the database. These should
     * generally not be held open very long in order to prevent
     * deadlocks
     */
    public function beginTransaction() {
        if (!$this->pendingTransaction) {
            $this->connector->beginTransaction();
            $this->pendingTransaction = true;
        }
    }


    /**
     * Commit a transaction as complete
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
     * Compile and run a SQL expression on the database
     *
     * @param IQuery Query to compile
     * @return ResultSet
     */
    public function query(IQuery $query) {
        $sql = $this->dialect->compile($query);
        $params = $query->getParameters();
        return $this->execute($sql, $params);
    }


    /**
     * Execute raw SQL on the database connection
     *
     * @param string $sql Statement string
     * @param array $params Params to bind to statement
     * @return ResultSet
     */
    public function execute($sql, $params = array()) {
        $this->echoQuery($sql);
        $statement = $this->connector->prepare($sql);

        foreach ($params as $param) {
            $statement->bindValue($param->getName(), $param->getValue(), $param->getDataType());
        }

        $statement->execute();
        return new ResultSet($this->connector, $statement);
    }


    /**
     * Revert a pending transaction on the database
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
