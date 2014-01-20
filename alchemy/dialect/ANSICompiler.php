<?php

namespace Alchemy\dialect;
use Alchemy\expression as expr;

class ANSICompiler extends Compiler {

    /**
     * Always returns the same auto-generated string for a given object
     *
     * @param  Scalar|Table $obj key
     * @return string            alias
     */
    public function alias($obj) {
        $fn = $this->getFunction($obj, 'Alias_');
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


    public function BinaryExpression($obj) {
        $elements = $this->compile($obj->listElements());
        return implode(' ', $elements);
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


    public function CompoundExpression($obj) {
        $elements = $this->compile($obj->listElements());
        $elements = implode(' ', $elements);
        return "({$elements})";
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
        $fn = $this->getFunction($obj, 'Create_', true);
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


    public function Create_Index($table, $name, $columns) {
        return "KEY {$name} ({$columns})";
    }


    public function Create_Foreign($table, $name, $columns) {
        return "FOREIGN KEY ({$columns}) REFERENCES {$name} ({$columns})";
    }


    public function Create_Integer($obj) {
        return "INT({$obj->getSize()})";
    }


    public function Create_Key($obj) {
        $fn = $this->getFunction($obj, 'Create_', true);

        $columns = $obj->listColumns();
        $column = reset($columns);
        $table = $column->getTable();

        $columns = array_map(function($column) {
            return $column->getName();
        }, $columns);

        $columns = implode(", ", $columns);

        return call_user_func($fn, $table->getName(), $obj->getName(), $columns);
    }


    public function Create_MediumInt($obj) {
        return "MEDIUMINT({$obj->getSize()})";
    }


    public function Create_Primary($table, $name, $columns) {
        return "PRIMARY KEY ({$columns})";
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


    public function Create_Unique($table, $name, $columns) {
        return "UNIQUE KEY {$name} ({$columns})";
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


    public function InclusiveExpression($obj) {
        $left  = $this->compile($obj->getLeft());
        $elements = $this->compile($obj->listElements());
        $elements = implode(', ', $elements);
        return "{$left} IN ({$elements})";
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


    public function Operator($obj) {
        return "{$obj->getType()}";
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