<?php

namespace Alchemy\expression;
use Alchemy\util\Monad;


/**
 * Abstract base class for representing a query
 */
abstract class Query extends Element implements IQuery {
    protected $columns = array();
    protected $joins = array();
    protected $where;
    protected $limit;
    protected $offset;


    /**
     * Returns an instance of the called query type wrapped
     * in an Monad
     *
     * @return Monad(Query)
     */
    public static function init() {
        $cls = get_called_class();
        return new Monad(new $cls());
    }


    public function __construct() {
        $parts = explode('\\', get_called_class());
        $cls = array_pop($parts);
        $this->addTag("sql.compile", $cls);
    }


    /**
     * Add a column to the query
     *
     * @param IQueryValue $column
     */
    public function column($column) {
       $this->columns[] = $column;
    }


    /**
     * Add multiple columns to the query by providing
     * multiple arguments. See {@link Query::column()}
     */
    public function columns() {
        if (func_num_args() == 0) {
            return $this->columns;
        }

        $columns = func_get_args();
        $columns = is_array($columns[0]) ? $columns[0] : $columns;

        foreach ($columns as $column) {
            $this->column($column);
        }
    }


    /**
     * Recursively get all scalar parameters used by this expression
     *
     * @return array array(Scalar, Scalar, ...)
     */
    public function getParameters() {
        $params = $this->where ? $this->where->getParameters() : array();

        if ($this->limit) {
            $params[] = $this->limit;
        }

        if ($this->offset) {
            $params[] = $this->offset;
        }

        foreach ($this->columns as $column) {
            if ($column instanceof Scalar) {
                $params[] = $column;
            }
        }

        foreach ($this->joins as $expression) {
            $params = array_merge($params, $expression->getParameters());
        }

        return $params;
    }


    /**
     * Add a join to the query
     *
     * @param Table $table
     * @param Expression $on
     * @param $direction Optional join direction
     * @param $type Optional join type
     */
    public function join($table, Expression $on, $direction = null, $type = null) {
        $direction = $direction ?: Join::LEFT;
        $type = $type ?: Join::INNER;
        $this->joins[] = new Join($direction, $type, $table, $on);
    }


    public function joins() {
        return $this->joins;
    }


    /**
     * Shortcut for doing an OUTER JOIN
     *
     * @param Table $table
     * @param Expression $on
     * @param $direction Optional join direction
     */
    public function outerJoin($table, Expression $on, $direction = null) {
        return $this->join($table, $on, $direction, Join::OUTER);
    }


    /**
     * Set the Query's WHERE expression. Calling this
     * multiple times will overwrite the previous expressions.
     * You should instead call this once with a CompoundExpression.
     *
     * @param Expression $expr
     */
    public function where(Expression $expr = null) {
        if (is_null($expr)) {
            return $this->where;
        }

        $this->where = $expr;
    }



    /**
     * Provide limit / offset to query.
     *
     * @param integer $a Query offset if $b is provided; else query limit.
     * @param integer $b Query limit.
     */
    public function limit($a = null, $b = null) {
        if (is_null($a) && is_null($b)) {
            return array($this->offset, $this->limit);
        }

        $a = is_null($a) ? null : new Scalar($a);
        $b = is_null($b) ? null : new Scalar($b);

        if (is_null($b)) {
            $this->limit = $a;
            $this->offset = null;
        } else {
            $this->limit = $b;
            $this->offset = $a;
        }
    }
}
