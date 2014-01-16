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
        if ($obj instanceof expr\Scalar) {
            return "p" . $obj->getID();
        } elseif ($obj instanceof expr\Table) {
            return strtolower(substr($obj->getName(), 0, 2)) . $obj->getID();
        }

        throw new Expection("Can't alias type " . get_class($obj));
    }


    public function BinaryExpression(expr\BinaryExpression $obj) {
        $elements = $this->compile($obj->listElements());
        return implode(' ', $elements);
    }


    public function Column(expr\Column $obj) {
        $column = $obj->getName();

        if ($this->getConfig('alias_tables')) {
            $column = "{$this->alias($obj->getTable())}.$column";
        }

        if ($this->getConfig('alias_columns')) {
            $column = "$column as {$obj->getAlias()}";
        }

        return $column;
    }


    public function CompoundExpression(expr\CompoundExpression $obj) {
        $elements = $this->compile($obj->listElements());
        $elements = implode(' ', $elements);
        return "({$elements})";
    }


    public function Create(expr\Create $obj) {
        $table = $obj->getTable();

        $columns = $this->map('Create_Column', $table->listColumns());
        $indexes = $this->map('Create_Index',  $table->listIndexes());
        $parts = implode(', ', array_merge($columns, $indexes));

        return "CREATE TABLE IF NOT EXISTS {$table->getName()} ({$parts})";
    }


    public function Create_BigInt(expr\BigInt $obj) {
        return "BIGINT({$obj->getSize()})";
    }


    public function Create_Binary(expr\Binary $obj) {
        return "BINARY({$obj->getSize()})";
    }


    public function Create_Blob(expr\Blob $obj) {
        return "BLOB";
    }


    public function Create_Bool(expr\Bool $obj) {
        return "BOOL";
    }


    public function Create_Char(expr\Char $obj) {
        return "CHAR({$obj->getSize()})";
    }


    public function Create_Column(expr\Column $obj) {
        $fn = $this->getFunction($obj, 'Create_', true);
        $type = call_user_func($fn, $obj);
        $null = $obj->isNotNull() ? "NOT NULL" : "NULL";

        return "{$obj->getName()} {$type} {$null}";
    }


    public function Create_Date(expr\Date $obj) {
        return "DATE";
    }


    public function Create_Datetime(expr\Datetime $obj) {
        return "DATETIME";
    }


    public function Create_Decimal(expr\Decimal $obj) {
        return "DECIMAL({$obj->getPrecision()}, {$obj->getScale()})";
    }


    public function Create_Float(expr\Float $obj) {
        return "FLOAT({$obj->getPrecision()})";
    }


    public function Create_Index() {
        return "";
    }


    public function Create_Integer(expr\Integer $obj) {
        return "INT({$obj->getSize()})";
    }


    public function Create_MediumInt(expr\MediumInt $obj) {
        return "MEDIUMINT({$obj->getSize()})";
    }


    public function Create_PrimaryKey() {
        return "";
    }


    public function Create_SmallInt(expr\SmallInt $obj) {
        return "SMALLINT({$obj->getSize()})";
    }


    public function Create_String(expr\String $obj) {
        return "VARCHAR({$obj->getSize()})";
    }


    public function Create_Time(expr\Time $obj) {
        return "TIME";
    }


    public function Create_Timestamp(expr\Timestamp $obj) {
        return "TIMESTAMP";
    }


    public function Create_TinyInt(expr\TinyInt $obj) {
        return "TINYINT({$obj->getSize()})";
    }


    public function Delete(expr\Delete $obj) {
        $alias = $this->getConfig('alias_tables') ? $this->alias($obj->from()) : '';

        $parts = array(
            "DELETE", $alias,
            "FROM {$this->compile($obj->from())}",
            $this->Query_Join($obj),
            $this->Query_Where($obj),
            $this->Query_Limit($obj));

        return implode(' ', array_filter($parts));
    }


    public function Drop(expr\Drop $obj) {
        return "DROP TABLE IF EXISTS {$obj->getTable()->getName()}";
    }


    public function InclusiveExpression(expr\InclusiveExpression $obj) {
        $left  = $this->compile($obj->getLeft());
        $elements = $this->compile($obj->listElements());
        $elements = implode(', ', $elements);
        return "{$left} IN ({$elements})";
    }


    public function Insert(expr\Insert $obj) {
        $columns = $this->compile($obj->columns());
        $rows    = $this->compile($obj->rows());

        $rows    = array_map(function($row) {
            return "(" . implode(", ", $row) . ")";
        }, $rows);

        $columns = implode(", ", $columns);
        $rows    = implode(", ", $rows);

        return "INSERT INTO {$obj->into()->getName()} ({$columns}) VALUES {$rows}";
    }


    public function Join(expr\Join $obj) {
        $table = $this->Table($obj->getTable());
        $on = $this->compile($obj->getOn());
        return "{$obj->getDirection()} {$obj->getType()} JOIN {$table} ON {$on}";
    }


    public function Operator(expr\Operator $obj) {
        return "{$obj->getType()}";
    }


    public function Scalar(expr\Scalar $obj) {
        return ":{$this->alias($obj)}";
    }


    public function Select(expr\Select $obj) {
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


    public function Table(expr\Table $obj) {
        if ($this->getConfig('alias_tables')) {
            return "{$obj->getName()} {$this->alias($obj)}";
        }

        return $obj->getName();
    }


    public function Update(expr\Update $obj) {
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


    public function Query_Join(expr\Query $obj) {
        return implode(' ', $this->map('Join', $obj->joins()));
    }


    public function Query_Limit(expr\Query $obj) {
        list($offset, $limit) = $this->compile($obj->limit());

        if (!$limit && !$offset) {
            return "";
        } elseif (!$offset) {
            return "LIMIT {$limit}";
        }

        return "LIMIT {$offset}, {$limit}";
    }


    public function Query_Where(expr\Query $obj) {
        $where = $this->compile($obj->where());
        return $where ? "WHERE {$where}" : "";
    }
}