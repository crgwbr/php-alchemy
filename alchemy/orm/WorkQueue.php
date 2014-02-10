<?php

namespace Alchemy\orm;
use Alchemy\core\query\Insert;
use Alchemy\core\query\Update;
use Alchemy\core\query\Delete;
use Alchemy\engine\IEngine;
use Alchemy\engine\ResultSet;
use Alchemy\util\Monad;
use Alchemy\util\promise\Promise;


/**
 * Queue for scheduling queries to be run later, in
 * the proper order
 */
class WorkQueue {
    protected $queue = array();


    /**
     * Delete data based on the given filters
     *
     * @param string $cls Class name of DataMapper subclass
     * @param array $pk array(ColumnName => IQueryValue) UPDATE Filters
     * @return Promise resolved when DELETE is actually run
     */
    public function delete($cls, $pk) {
        $table = $cls::table();

        // Build UPDATE SETs
        $query = Delete::init()->from($table);

        // Filter the update by building a complex CompoundExpression
        $where = null;
        foreach ($pk as $name => $value) {
            if (!$where) {
                $where = $table->$name->equal($value);
            } else {
                $where = $where->and($table->$name->equal($value));
            }
        }
        $query = $query->where($where);

        return $this->push($query);
    }


    /**
     * Flush all pending queries to the database
     *
     * @param IEngine $engine
     */
    public function flush(IEngine $engine) {
        while ($item = array_shift($this->queue)) {
            list($query, $promise) = $item;
            try {
                $r = $engine->query($query);
            } catch (\Exception $e) {
                throw $e;
            }
            $promise->resolve($r);
        }
    }


    /**
     * INSERT data based on the given DataMapper class
     * and a record of properties.
     *
     * @param string $cls Class name of DataMapper subclass
     * @param array $data Array of properties to send in the INSERT
     * @return Promise resolved when INSERT is actual run
     */
    public function insert($cls, $data) {
        $table = $cls::table();

        $scalars = array();
        $columns = array();
        foreach ($data as $name => $value) {
            $columns[] = $table->$name;
            $scalars[] = $table->{$name}->schema()->encode($value);
        }

        $query = Insert::init()->columns($columns)
                               ->into($table)
                               ->row($scalars);

        return $this->push($query);
    }


    /**
     * Push a query onto the end of the queue
     *
     * @param Monad Query to push
     * @return Promise resolved when query is actual run
     */
    protected function push(Monad $query) {
        $promise = new Promise(null, 'Alchemy\engine\ResultSet');
        $this->queue[] = array($query->unwrap(), $promise);
        return $promise;
    }


    /**
     * UPDATE data based on the given DataMapper class, a
     * record of properties to update, and an array keys
     *
     * @param string $cls Class name of DataMapper subclass
     * @param array $pk array(ColumnName => IQueryValue) UPDATE Filters
     * @param array $data Array of properties to send in the INSERT
     * @return Promise resolved when INSERT is actually run
     */
    public function update($cls, $pk, $data) {
        $table = $cls::table();

        // Build UPDATE SETs
        $query = Update::init()->table($table);
        foreach ($data as $name => $value) {
            $query = $query->set($table->{$name}, $table->{$name}->schema()->encode($value));
        }

        // Filter the update by building a complex CompoundExpression
        $where = null;
        foreach ($pk as $name => $value) {
            if (!$where) {
                $where = $table->$name->equal($value);
            } else {
                $where = $where->and($table->$name->equal($value));
            }
        }
        $query = $query->where($where);

        return $this->push($query);
    }
}
