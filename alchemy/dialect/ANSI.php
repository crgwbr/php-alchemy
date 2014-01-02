<?php

namespace Alchemy\dialect;
use Exception;


abstract class ANSI_DialectBase {
    protected $data;


    public function __construct($data) {
        $this->data = $data;
    }


    public function __get($name) {
        if (!array_key_exists($name, $this->data)) {
            throw new Exception("Property {$name} doesn't exist");
        }

        return $this->data[$name];
    }


    abstract public function __toString();
}



abstract class ANSI_Column extends ANSI_DialectBase {

    public function __toString() {
        return "{$this->tableAlias}.{$this->name}";
    }


    abstract public function definition();


    public function getName() {
        return $this->name;
    }


    public function getSelectDeclaration() {
        return "{$this} as {$this->alias}";
    }
}



abstract class ANSI_Query extends ANSI_DialectBase {

    protected function getJoinSQL() {
        return implode(" ", $this->joins);
    }


    protected function getWhereSQL() {
        if (!$this->where) {
            return "";
        }

        return "WHERE {$this->where}";
    }
}



class ANSI_BinaryExpression extends ANSI_DialectBase {

    public function __toString() {
        return "{$this->left} {$this->operator} {$this->right}";
    }
}


class ANSI_Bool extends ANSI_Column {
    public function definition() {
        $sql = "{$this->name} BOOL ";
        $sql .= $this->kwargs['null'] ? "NULL" : "NOT NULL";
        return $sql;
    }
}



class ANSI_CompoundExpression extends ANSI_DialectBase {

    public function __toString() {
        $expr = implode(" ", $this->components);
        return "({$expr})";
    }
}



class ANSI_Create extends ANSI_DialectBase {

    public function __toString() {
        $table = $this->table->getName();

        $columns = array();
        foreach ($this->table->columns as $column) {
            $columns[] = $column->definition();
        }
        $columns = implode(", ", $columns);

        $sql = "CREATE TABLE IF NOT EXISTS {$table} ({$columns})";
        return $sql;
    }
}



class ANSI_Drop extends ANSI_DialectBase {

    public function __toString() {
        $table = $this->table->getName();

        $sql = "DROP TABLE IF EXISTS {$table}";
        return $sql;
    }
}



class ANSI_InclusiveExpression extends ANSI_DialectBase {

    public function __toString() {
        $in = implode(", ", $this->in);
        return "{$this->left} IN ({$in})";
    }
}



class ANSI_Insert extends ANSI_Query {

    public function __toString() {
        $columns = $this->getColumnSQL();
        $rows = $this->getRowSQL();

        $str = "INSERT INTO {$this->into->getName()} ($columns) VALUES {$rows}";

        $str = trim($str);
        return $str;
    }


    protected function getColumnSQL() {
        if (count($this->columns) <= 0) {
            throw new Exception("No columns to insert");
        }

        $columns = array_map(function($column) {
            return $column->getName();
        }, $this->columns);
        $columns = implode(", ", $columns);
        return $columns;
    }


    protected function getRowSQL() {
        if (count($this->rows) <= 0) {
            throw new Exception("No rows to insert");
        }

        $rows = array();
        foreach ($this->rows as $row) {
            $rows[] = implode(", ", $row);
        }
        $rows = implode("), (", $rows);
        return "({$rows})";
    }
}



class ANSI_Integer extends ANSI_Column {
    public function definition() {
        $sql = "{$this->name} INT({$this->args[0]}) ";
        $sql .= $this->kwargs['null'] ? "NULL" : "NOT NULL";
        return $sql;
    }
}



class ANSI_Join extends ANSI_DialectBase {

    public function __toString() {
        return "{$this->direction} {$this->type} JOIN {$this->table} ON {$this->on}";
    }
}



class ANSI_Operator extends ANSI_DialectBase {

    public function __toString() {
        return $this->type;
    }
}



class ANSI_Scalar extends ANSI_DialectBase {

    public function __toString() {
        return '?';
    }
}



class ANSI_Select extends ANSI_Query {

    public function __toString() {
        $columns = $this->getColumnSQL();
        $from = $this->getFromSQL();
        $joins = $this->getJoinSQL();
        $where = $this->getWhereSQL();

        $str = "SELECT {$columns} {$from} {$joins} {$where}";

        $str = trim($str);
        return $str;
    }


    protected function getColumnSQL() {
        if (count($this->columns) <= 0) {
            throw new Exception("No columns to select");
        }

        $columns = array_map(function($column) {
            return $column->getSelectDeclaration();
        }, $this->columns);
        $columns = implode(", ", $columns);
        return $columns;
    }


    protected function getFromSQL() {
        return "FROM {$this->from}";
    }
}



class ANSI_String extends ANSI_Column {
    public function definition() {
        $sql = "{$this->name} VARCHAR({$this->args[0]}) ";
        $sql .= $this->kwargs['null'] ? "NULL" : "NOT NULL";
        return $sql;
    }
}



class ANSI_Table extends ANSI_DialectBase {

    public function __toString() {
        return "{$this->name} {$this->alias}";
    }


    public function getName() {
        return $this->name;
    }


    public function getAlias() {
        return $this->alias;
    }
}



class ANSI_Timestamp extends ANSI_Column {
    public function definition() {
        $sql = "{$this->name} TIMESTAMP ";
        $sql .= $this->kwargs['null'] ? "NULL" : "NOT NULL";
        return $sql;
    }
}
