<?php

namespace Alchemy\dialect;
use Alchemy\expression as expr;

class ANSICompiler extends Compiler {
    protected static $expr_formats = array(
        // operators
        'add'       => '%s + %s',
        'sub'       => '%s - %s',
        'mult'      => '%s * %s',
        'div'       => '%s / %s',
        'mod'       => 'MOD(%s, %s)',
        'abs'       => 'ABS(%s)',
        'ceil'      => 'CEIL(%s)',
        'exp'       => 'EXP(%s)',
        'floor'     => 'FLOOR(%s)',
        'ln'        => 'LN(%s)',
        'sqrt'      => 'SQRT(%s)',
        'extract'   => 'EXTRACT(%s FROM %s)',
        'interval'  => 'INTERVAL %s %s',
        'now'       => 'NOW()',
        'lower'     => 'LOWER(%s)',
        'upper'     => 'UPPER(%s)',
        'convert'   => 'CONVERT(%s USING %s)',
        'translate' => 'TRANSLATE(%s USING %s)',
        'concat'    => '%s || %s',
        'coalesce'  => 'COALESCE(%1//, /)',

        // predicates
        'equal'     => '%s = %s',
        'lt'        => '%s < %s',
        'gt'        => '%s > %s',
        'ne'        => '%s != %s',
        'le'        => '%s <= %s',
        'ge'        => '%s >= %s',
        'between'   => '%s BETWEEN %s AND %s',
        'isNull'    => '%s IS NULL',
        'like'      => '%s LIKE %s',
        'in'        => '%s IN (%2//, /)',
        'and'       => '(%// AND /)',
        'or'        => '(%// OR /)',
        'not'       => 'NOT (%s)');

    protected static $index_formats = array(
        'Index'      => "KEY %s (%3$//, /)",
        'UniqueKey'  => "UNIQUE KEY %s (%3$//, /)",
        'PrimaryKey' => "PRIMARY KEY (%3$//, /)");

    /**
     * Always returns the same auto-generated string for a given object
     *
     * @param  Element $obj key
     * @return string            alias
     */
    public function alias($obj) {
        $fn = $this->getFunction($obj, 'sql.compile', 'Alias_');
        return call_user_func($fn, $obj);
    }


    public function Alias_Column($obj) {
        return $obj->getName();
    }


    public function Alias_Scalar($obj) {
        return "p" . $this->aliasID('scalar', $obj->getID());
    }


    public function Alias_Table($obj) {
        return strtolower(substr($obj->getName(), 0, 2)) . $obj->getID();
    }


    public function Column($obj) {
        $column = $obj->getName();

        if ($this->getConfig('alias_tables')) {
            $column = "{$this->alias($obj->getTable())}.$column";
        }

        if ($this->getConfig('alias_columns')) {
            $column = "$column as {$this->alias($obj)}";
        }

        return $column;
    }


    public function Create($obj) {
        $table = $obj->getTable();

        $columns = $this->map('Create_Column', $table->listColumns());
        $columns = array_values($columns);

        $indexes = $this->map('Create_Key', $table->listIndexes());
        $indexes = array_values($indexes);

        $parts = implode(', ', array_merge($columns, $indexes));

        return "CREATE TABLE IF NOT EXISTS {$table->getName()} ({$parts})";
    }


    public function Create_BigInt($obj) {
        return "BIGINT({$obj->getSize()})";
    }


    public function Create_Binary($obj) {
        return "BINARY({$obj->getSize()})";
    }


    public function Create_Blob($obj) {
        return "BLOB";
    }


    public function Create_Bool($obj) {
        return "BOOL";
    }


    public function Create_Char($obj) {
        return "CHAR({$obj->getSize()})";
    }


    public function Create_Column($obj) {
        $fn = $this->getFunction($obj, 'sql.create', 'Create_');
        $type = call_user_func($fn, $obj);
        $null = $obj->isNotNull() ? "NOT NULL" : "NULL";

        return "{$obj->getName()} {$type} {$null}";
    }


    public function Create_Date($obj) {
        return "DATE";
    }


    public function Create_Datetime($obj) {
        return "DATETIME";
    }


    public function Create_Decimal($obj) {
        return "DECIMAL({$obj->getPrecision()}, {$obj->getScale()})";
    }


    public function Create_Float($obj) {
        return "FLOAT({$obj->getPrecision()})";
    }


    public function Create_Index($obj) {
        $format = static::$index_formats[$obj->getType()];
        $elements = array($obj->getName(), $obj->getTable()->getName(),
            $this->compile($obj->listColumns()));

        return $this->format($format, $elements);
    }


    public function Create_ForeignKey($obj) {
        $columns = $this->compile($obj->listColumns());
        $columns = implode(', ', $columns);

        $sources = $this->compile($obj->listSources());
        $sources = implode(', ', $sources);

        $table = $obj->getSourceTable()->getName();

        return "FOREIGN KEY ({$columns}) REFERENCES {$table} ({$sources})";
    }


    public function Create_Integer($obj) {
        return "INT({$obj->getSize()})";
    }


    public function Create_Key($obj) {
        $fn = $this->getFunction($obj, 'sql.create', 'Create_');
        return call_user_func($fn, $obj);
    }


    public function Create_MediumInt($obj) {
        return "MEDIUMINT({$obj->getSize()})";
    }


    public function Create_SmallInt($obj) {
        return "SMALLINT({$obj->getSize()})";
    }


    public function Create_String($obj) {
        return "VARCHAR({$obj->getSize()})";
    }


    public function Create_Time($obj) {
        return "TIME";
    }


    public function Create_Timestamp($obj) {
        return "TIMESTAMP";
    }


    public function Create_TinyInt($obj) {
        return "TINYINT({$obj->getSize()})";
    }


    public function Delete($obj) {
        $alias = $this->getConfig('alias_tables') ? $this->alias($obj->from()) : '';

        $parts = array(
            "DELETE", $alias,
            "FROM {$this->compile($obj->from())}",
            $this->Query_Join($obj),
            $this->Query_Where($obj),
            $this->Query_Limit($obj));

        return implode(' ', array_filter($parts));
    }


    public function Drop($obj) {
        return "DROP TABLE IF EXISTS {$obj->getTable()->getName()}";
    }


    public function Insert($obj) {
        $columns = $this->compile($obj->columns());
        $rows    = $this->compile($obj->rows());

        $rows    = array_map(function($row) {
            return "(" . implode(", ", $row) . ")";
        }, $rows);

        $columns = implode(", ", $columns);
        $rows    = implode(", ", $rows);

        return "INSERT INTO {$obj->into()->getName()} ({$columns}) VALUES {$rows}";
    }


    public function Join($obj) {
        $table = $this->compile($obj->getTable());
        $on    = $this->compile($obj->getOn());
        return "{$obj->getDirection()} {$obj->getType()} JOIN {$table} ON {$on}";
    }


    public function Expression($obj) {
        $format = static::$expr_formats[$obj->getType()];
        $elements = $this->compile($obj->getElements());

        return $this->format($format, $elements);
    }


    public function Scalar($obj) {
        return ":{$this->alias($obj)}";
    }


    public function Select($obj) {
        $columns = $this->compile($obj->columns(),
            array('alias_columns' => true));
        $columns = implode(", ", $columns);

        $from = $this->compile($obj->from());

        $parts = array(
            "SELECT {$columns}",
            $from ? "FROM {$from}" : "",
            $this->Query_Join($obj),
            $this->Query_Where($obj),
            $this->Query_Limit($obj));

        return implode(' ', array_filter($parts));
    }


    public function Table($obj) {
        if ($this->getConfig('alias_tables')) {
            return "{$obj->getName()} {$this->alias($obj)}";
        }

        return $obj->getName();
    }


    public function Update($obj) {
        $fn = function($value) {
            list($column, $scalar) = $value;
            return "{$column} = {$scalar}";
        };

        $table = $this->compile($obj->table());
        $sets  = $this->compile($obj->sets());
        $sets  = implode(", ", array_map($fn, $sets));

        $parts = array(
            "UPDATE {$table} SET {$sets}",
            $this->Query_Join($obj),
            $this->Query_Where($obj),
            $this->Query_Limit($obj));

        return implode(' ', array_filter($parts));
    }


    public function Query_Join($obj) {
        return implode(' ', $this->compile($obj->joins()));
    }


    public function Query_Limit($obj) {
        list($offset, $limit) = $this->compile($obj->limit());

        if (!$limit && !$offset) {
            return "";
        } elseif (!$offset) {
            return "LIMIT {$limit}";
        }

        return "LIMIT {$offset}, {$limit}";
    }


    public function Query_Where($obj) {
        $where = $this->compile($obj->where());
        return $where ? "WHERE {$where}" : "";
    }
}