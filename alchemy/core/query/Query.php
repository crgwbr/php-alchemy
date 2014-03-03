<?php

namespace Alchemy\core\query;
use Alchemy\core\Element;
use Alchemy\util\Monad;


/**
 * Represents a generalized SQL query
 */
class Query extends Element implements IQuery {
    protected $columns = array();
    protected $joins = array();
    protected $where;
    protected $limit;
    protected $offset;
    protected $table;


    public function __construct($type, TableRef $table) {
        parent::__construct($type);
        $this->table = $table;
    }


    public function __set($name, $value) {
        if (!($value instanceof Element) || !$value->getTag('expr.value')) {
            $schema = $this->table->schema();
            $value = $schema->hasColumn($name)
                ? $schema->getColumn($name)->encode($value)
                : new Scalar($value);
        }

        $this->columns[$name] = $value;
    }


    /**
     * Add multiple columns to the query by providing
     * multiple arguments. See {@link Query::column()}
     * Pass, ColumnRefs, ["Name", Value] pairs, or a
     * single array of either as necessary.
     *
     * @param  array|ColumnRef
     *         ...
     * @return array           current column list if no arguments
     */
    public function columns() {
        if (func_num_args() == 0) {
            return $this->columns;
        }

        $columns = func_get_args();
        $columns = (count($columns) == 1 && is_array($columns[0])) ? $columns[0] : $columns;

        foreach ($columns as $name => $column) {
            if (is_integer($name) && is_array($column)) {
                $name   = $column[0];
                $column = $column[1];
            }

            $name = is_string($name) ? $name : $column->name();
            $this->__set($name, $column);
        }

        return $this;
    }


    /**
     * Recursively get all scalar parameters used by this expression
     *
     * @return array array(Scalar, Scalar, ...)
     */
    public function parameters() {
        $params = $this->where ? $this->where->parameters() : array();

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

        foreach ($this->joins as $join) {
            $params = array_merge($params, $join->parameters());
        }

        return $params;
    }


    /**
     * Add a join to the query
     *
     * @param Table $table
     * @param Predicate $on
     * @param $direction Optional join direction
     * @param $type Optional join type
     */
    public function join($table, Predicate $on = null, $direction = null, $type = null) {
        $direction = $direction ?: Join::LEFT;
        $type = $type ?: Join::INNER;

        $this->joins[] = new Join($direction, $type, $table, $on);
        return $this;
    }


    public function joins() {
        return $this->joins;
    }


    /**
     * Shortcut for doing an OUTER JOIN
     *
     * @param Table $table
     * @param Predicate $on
     * @param $direction Optional join direction
     */
    public function outerJoin($table, Predicate $on, $direction = null) {
        return $this->join($table, $on, $direction, Join::OUTER);
    }


    /**
     * Return the table this query applies to
     *
     * @return TableRef $table
     */
    public function table() {
        return $this->table;
    }


    /**
     * Set the Query's WHERE expression. Calling this
     * multiple times will overwrite the previous expressions.
     * You should instead call this once with a CompoundExpression.
     *
     * @param Expression $expr
     */
    public function where($expr = false) {
        if ($expr === false) {
            return $this->where;
        }

        $this->where = is_null($expr) ? null : Predicate::ALL(func_get_args());
        return $this;
    }


    /**
     * Limit number of rows affected by query.
     *
     * @param integer $limit Query limit.
     */
    public function limit($limit = false) {
        if ($limit === false) {
            return $this->limit;
        }

        $this->limit = is_null($limit) ? null : new Scalar($limit);
        return $this;
    }


    /**
     * Offset start of rows affected by query.
     *
     * @param integer $offset Query offset.
     */
    public function offset($offset = false) {
        if ($offset === false) {
            return $this->offset;
        }

        $this->offset = is_null($offset) ? null : new Scalar($offset);
        return $this;
    }
}
