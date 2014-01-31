<?php

namespace Alchemy\expression;
use Alchemy\util\promise\IPromisable;


/**
 * Abstract base class for representing a column in SQL
 */
class Column extends TableElement implements IQueryValue, IPromisable {

    /**
     * Retrieve the column for a reference like 'Table.Column',
     * or 'self.Column' if a $self table is provided.
     *
     * @param  string $column column reference
     * @param  Table  $self   table to use for relative references (optional)
     * @return Column
     */
    public static function find($column, $self = null) {
        list($ref, $col) = explode('.', $column) + array('', '');

        $reftable = ($ref == 'self' || $ref == '') ? $self : Table::find($ref);
        if (!($reftable instanceof Table)) {
            throw new \Exception("Cannot find Table '{$ref}'.");
        }

        return $reftable->{$col};
    }


    public static function list_promisable_methods() {
        $NS = __NAMESPACE__;
        return array(
            'copy'     => "$NS\Column",
            'getTable' => "$NS\Table");
    }


    /**
     * Build and return a Predicate by comparing this
     * column to another IQueryValue
     *
     * @param $name Operator Name: and, or
     * @param $args array([0] => IQueryValue, ...) IQueryValue to compare to
     */
    public function __call($name, $args) {
        foreach ($args as &$value) {
            if (!($value instanceof IQueryValue)) {
                $value = new Scalar($value);
            }
        }

        array_unshift($args, $this);

        return new Predicate($name, $args);
    }


    public function __construct($args = array(), $table = null, $name = '', $type = '') {
        parent::__construct($args, $table, $name, $type);

        $this->addTag("sql.compile", "Column");
        $this->addTag("expr.value");
    }


    /**
     * Decode a value from the RDBMS into a PHP value
     *
     * @param mixed $value
     * @return string
     */
    public function decode($value) {
        $def = self::get_definition($this->type);
        return $def['decode']($this, $value);
    }


    /**
     * Encode a PHP value into something usable for the RDBMS.
     *
     * @param mixed $value
     * @return Scalar
     */
    public function encode($value) {
        $def = self::get_definition($this->type);
        return $def['encode']($this, $value);
    }


    /**
     * Get the ForeignKey constraint, if applicable, on this Column.
     *
     * @return ForeignKey
     */
    public function getForeignKey() {
        if ($this->args['foreign_key']) {
            return Index::ForeignKey(array(array($this), array($this->args['foreign_key'])), $this->table, $this->name);
        }
    }


    /**
     * Get the Index, if applicable, on this Column.
     *
     * @return Index
     */
    public function getIndex() {
        if ($this->args['unique']) {
            return Index::UniqueKey($this, $this->table, $this->name);
        } elseif ($this->args['index']) {
            return Index::Index($this, $this->table, $this->name);
        }
    }


    public function getArg($arg) {
        return array_key_exists($arg, $this->args) ? $this->args[$arg] : null;
    }


    /**
     * Return true if this column can not be null
     *
     * @return bool
     */
    public function isNotNull() {
        return !$this->args['null'];
    }


    /**
     * Return true if this column is part of the primary key
     *
     * @return bool
     */
    public function isPrimaryKeyPart() {
        return $this->args['primary_key'];
    }
}
