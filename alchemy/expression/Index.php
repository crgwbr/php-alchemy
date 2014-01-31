<?php

namespace Alchemy\expression;


/**
 * Class for representing an index in SQL
 */
class Index extends TableElement {

    protected $columns;


    /**
     * Get the index name
     *
     * @return string
     */
    public function getName() {
        if ($this->name) {
            return $this->name;
        }

        $this->resolve();

        $names = array();
        foreach ($this->columns as $column) {
            $names[] = $column->getName();
        }

        return implode('_', $names);
    }


    /**
     * List the columns used by this index
     *
     * @return array
     */
    public function listColumns() {
        $this->resolve();
        return $this->columns;
    }


    protected function resolve() {
        if ($this->columns) return;

        if (!isset($this->args[0]) || count($this->args[0]) == 0) {
            throw new \Exception("Index did not receive any columns.");
        }

        foreach ($this->args[0] as $column) {
            $this->columns[] = is_string($column)
                ? Column::find($column, $this->table)
                : $column;
        }
    }
}
