<?php

namespace Alchemy\dialect;
use Exception;


/**
 * Abstract base class for vernacular query chunks
 */
abstract class ANSI_DialectBase {
    protected $data;


    /**
     * Object constructor
     *
     * @param array $data
     */
    public function __construct(array $data) {
        $this->data = $data;
    }


    /**
     * Magic getter for keys of $this->data
     */
    public function __get($name) {
        if (!array_key_exists($name, $this->data)) {
            throw new Exception("Property {$name} doesn't exist");
        }

        return $this->data[$name];
    }


    /**
     * String Cast
     */
    abstract public function __toString();
}


/**
 * Abstract base class for representing a DB column
 */
abstract class ANSI_Column extends ANSI_DialectBase {

    /**
     * String Cast
     */
    public function __toString() {
        return "{$this->tableAlias}.{$this->name}";
    }


    /**
     * Column Definition for a create table statement
     *
     * @return string
     */
    abstract public function definition();


    /**
     * Column Name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }


    /**
     * Get configuration keyword argument
     *
     * @param string $name Arg Name
     * @return string|integer|bool
     */
    public function getKwarg($name) {
        return array_key_exists($name, $this->kwargs) ? $this->kwargs[$name] : null;
    }


    /**
     * Declaration for column select
     *
     * @return string
     */
    public function getSelectDeclaration() {
        return "{$this} as {$this->alias}";
    }
}



/**
 * Abstract class for representing a vernacular query
 */
abstract class ANSI_Query extends ANSI_DialectBase {

    /**
     * Get SQL for JOINs
     *
     * @return string
     */
    protected function getJoinSQL() {
        return implode(" ", $this->joins);
    }


    /**
     * Get SQL for WHERE
     *
     * @return string
     */
    protected function getWhereSQL() {
        if (!$this->where) {
            return "";
        }

        return "WHERE {$this->where}";
    }


    protected function getLimitSQL() {
        if (!$this->limit && !$this->offset) {
            return "";
        } elseif (!$this->offset) {
            return "LIMIT {$this->limit}";
        }

        return "LIMIT {$this->offset}, {$this->limit}";
    }
}



/**
 * Represents a binary (two argument) expression with a single operator
 */
class ANSI_BinaryExpression extends ANSI_DialectBase {

    /**
     * String Cast
     */
    public function __toString() {
        return "{$this->left} {$this->operator} {$this->right}";
    }
}



/**
 * Represents a boolean column
 */
class ANSI_Bool extends ANSI_Column {

    /**
     * @see ANSI_Column::definition()
     */
    public function definition() {
        $sql = "{$this->name} BOOL ";
        $sql .= $this->getKwarg('null') ? "NULL" : "NOT NULL";
        return $sql;
    }
}



/**
 * Represents a collection of other expressions conjoined
 * with either AND or OR
 */
class ANSI_CompoundExpression extends ANSI_DialectBase {

    /**
     * Stirng Cast
     */
    public function __toString() {
        $expr = implode(" ", $this->components);
        return "({$expr})";
    }
}



/**
 * Represents a CREATE statement
 */
class ANSI_Create extends ANSI_DialectBase {

    /**
     * String Cast
     */
    public function __toString() {
        $table = $this->table->getName();

        $columns = array();
        $pk = array();
        foreach ($this->table->columns as $column) {
            $columns[] = $column->definition();

            if ($column->getKwarg('primary_key')) {
                $pk[] = $column->getName();
            }
        }

        if (!empty($pk)) {
            $pk = implode(", ", $pk);
            $columns[] = "PRIMARY KEY ({$pk})";
        }

        $columns = implode(", ", $columns);
        $sql = "CREATE TABLE IF NOT EXISTS {$table} ({$columns})";
        return $sql;
    }
}



/**
 * Represents a DROP statement
 */
class ANSI_Drop extends ANSI_DialectBase {

    /**
     * String Cast
     */
    public function __toString() {
        $table = $this->table->getName();

        $sql = "DROP TABLE IF EXISTS {$table}";
        return $sql;
    }
}



/**
 * Represent a inclusive expression like: "table.column IN (?, ?, ?)"
 */
class ANSI_InclusiveExpression extends ANSI_DialectBase {

    /**
     * String Cast
     */
    public function __toString() {
        $in = implode(", ", $this->in);
        return "{$this->left} IN ({$in})";
    }
}



/**
 * Represent a INSERT statement
 */
class ANSI_Insert extends ANSI_Query {

    /**
     * String Cast
     */
    public function __toString() {
        $columns = $this->getColumnSQL();
        $rows = $this->getRowSQL();

        $str = "INSERT INTO {$this->into->getName()} ($columns) VALUES {$rows}";

        $str = trim($str);
        return $str;
    }


    /**
     * Get SQL for columns to insert
     *
     * @return string
     */
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


    /**
     * Get SQL for rows to insert
     *
     * @return string
     */
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



/**
 * Represent an integer column
 */
class ANSI_Integer extends ANSI_Column {

    /**
     * @see ANSI_Column::definition()
     */
    public function definition() {
        $sql = "{$this->name} INT({$this->args[0]}) ";
        $sql .= $this->getKwarg('null') ? "NULL" : "NOT NULL";
        return trim($sql);
    }
}



/**
 * Represent a JOIN clause
 */
class ANSI_Join extends ANSI_DialectBase {

    /**
     * String Cast
     */
    public function __toString() {
        return "{$this->direction} {$this->type} JOIN {$this->table} ON {$this->on}";
    }
}



/**
 * Represent a logical operator
 */
class ANSI_Operator extends ANSI_DialectBase {

    /**
     * String Cast
     */
    public function __toString() {
        return $this->type;
    }
}



/**
 * Represent a scalar value
 */
class ANSI_Scalar extends ANSI_DialectBase {

    /**
     * String Cast
     */
    public function __toString() {
        return '?';
    }
}



/**
 * Represent a SELECt statement
 */
class ANSI_Select extends ANSI_Query {

    /**
     * String Cast
     */
    public function __toString() {
        $parts = array(
            $this->getColumnSQL(),
            $this->getFromSQL(),
            $this->getJoinSQL(),
            $this->getWhereSQL(),
            $this->getLimitSQL());

        $str = "SELECT " . join(array_filter($parts), ' ');

        $str = trim($str);
        return $str;
    }


    /**
     * Get SQL for columns to SELECT
     *
     * @return string
     */
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


    /**
     * Get SQL for FROM
     */
    protected function getFromSQL() {
        return "FROM {$this->from}";
    }
}



/**
 * Represent a VARCHAR column
 */
class ANSI_String extends ANSI_Column {

    /**
     * @see ANSI_Column::definition()
     */
    public function definition() {
        $sql = "{$this->name} VARCHAR({$this->args[0]}) ";
        $sql .= $this->getKwarg('null') ? "NULL" : "NOT NULL";
        return $sql;
    }
}



/**
 * Represent a table
 */
class ANSI_Table extends ANSI_DialectBase {

    /**
     * Stirng Cast
     */
    public function __toString() {
        return "{$this->name} {$this->alias}";
    }


    /**
     * Get the table name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }


    /**
     * Return the table alias
     *
     * @return string
     */
    public function getAlias() {
        return $this->alias;
    }
}


/**
 * Represent a timestamp column
 */
class ANSI_Timestamp extends ANSI_Column {

    /**
     * @see ANSI_Column::definition()
     */
    public function definition() {
        $sql = "{$this->name} TIMESTAMP ";
        $sql .= $this->getKwarg('null') ? "NULL" : "NOT NULL";
        return $sql;
    }
}



/**
 * Represent an UPDATE statement
 */
class ANSI_Update extends ANSI_Query {

    /**
     * String Cast
     */
    public function __toString() {
        $sets = array();
        foreach ($this->values as $column => $value) {
            $sets = "{$column} = {$value}";
        }

        $sets = implode(", ", $sets);
        $joins = $this->getJoinSQL();
        $where = $this->getWhereSQL();

        $str = "UPDATE {$this->table} SET {$sets} {$joins} {$where}";
        $str = trim($str);
        return $str;
    }
}
