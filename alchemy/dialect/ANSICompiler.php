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
        'isnull'    => '%s IS NULL',
        'like'      => '%s LIKE %s',
        'in'        => '%s IN (%2//, /)',
        'and'       => '(%// AND /)',
        'or'        => '(%// OR /)',
        'not'       => 'NOT (%s)');

    protected static $schema_formats = array(
        // numerics
        'Bool'       => "BOOL",
        'Integer'    => "INT(%s)",
        'TinyInt'    => "TINYINT(%s)",
        'SmallInt'   => "SMALLINT(%s)",
        'MediumInt'  => "MEDIUMINT(%s)",
        'BigInt'     => "BIGINT(%s)",
        'Float'      => "FLOAT(%s)",
        'Decimal'    => "DECIMAL(%s, %s)",

        //strings
        'Blob'       => "BLOB",
        'Binary'     => "BINARY(%s)",
        'String'     => "VARCHAR(%s)",
        'Char'       => "CHAR(%s)",
        'Text'       => "TEXT(%s)",

        // datetimes
        'Date'       => "DATE",
        'Time'       => "TIME",
        'Datetime'   => "DATETIME",
        'Timestamp'  => "TIMESTAMP",

        // indexes
        'Index'      => "KEY %s (%3$//, /)",
        'UniqueKey'  => "UNIQUE KEY %s (%3$//, /)",
        'PrimaryKey' => "PRIMARY KEY (%3$//, /)");

    private $counters = array();
    private $aliases = array();


    public static function get_schema_format($type) {
        if (array_key_exists($type, static::$schema_formats)) {
            return static::$schema_formats[$type];
        }

        $parent = get_parent_class(get_called_class());
        if ($parent && method_exists($parent, 'get_schema_format')) {
            return $parent::get_schema_format($type);
        }
    }

    /**
     * Always returns the same auto-generated string for a given object
     *
     * @param  Element $obj key
     * @return string            alias
     */
    public function alias($obj) {
        $tag = $obj->getTag('sql.compile');
        $key = "{$tag}.{$obj->getID()}";

        if (!array_key_exists($key, $this->aliases)) {
            if (!array_key_exists($tag, $this->counters)) {
                $this->counters[$tag] = 0;
            }

            $id = $this->counters[$tag]++;
            $fn = $this->getFunction($obj, 'sql.compile', 'Alias_');
            $this->aliases[$key] = call_user_func($fn, $obj, $id);
        }

        return $this->aliases[$key];
    }


    public function Alias_ColumnRef($obj, $id) {
        return $obj->name();
    }


    public function Alias_Scalar($obj, $id) {
        return "p{$id}";
    }


    public function Alias_TableRef($obj, $id) {
        return strtolower(substr($obj->name(), 0, 2)) . ($id + 1);
    }


    public function Column($obj) {
        return $obj->getName();
    }


    public function ColumnRef($obj) {
        $column = $obj->name();

        if ($this->getConfig('alias_tables')) {
            $column = "{$this->alias($obj->table())}.$column";
        }

        if ($this->getConfig('alias_columns')) {
            $column = "$column as {$this->alias($obj)}";
        }

        return $column;
    }


    public function Create($obj) {
        $table = $obj->getTable();

        $parts = array_merge(
            array_values($table->listColumns()),
            array_values($table->listIndexes()));

        $parts = implode(', ', $this->map('Create_Element', $parts));

        return "CREATE TABLE IF NOT EXISTS {$table->getName()} ({$parts})";
    }


    public function Create_Column($obj) {
        $null = $obj->isNullable() ? "NULL" : "NOT NULL";

        if ($fn = $this->getFunction($obj, 'element.type', 'Create_')) {
            $type = call_user_func($fn, $obj);
        } else {
            $format = static::get_schema_format($obj->getType());
            $type = $this->format($format,
                array($obj->getArg(0), $obj->getArg(1)));
        }

        return "{$obj->getName()} {$type} {$null}";
    }


    public function Create_Index($obj) {
        $format = static::get_schema_format($obj->getType());
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


    public function Create_Element($obj) {
        $fn = $this->getFunction($obj, 'sql.create', 'Create_');
        return call_user_func($fn, $obj);
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

        return "INSERT INTO {$obj->into()->name()} ({$columns}) VALUES {$rows}";
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


    public function TableRef($obj) {
        if ($this->getConfig('alias_tables')) {
            return "{$obj->name()} {$this->alias($obj)}";
        }

        return $obj->name();
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