<?php

namespace Alchemy\expression;


/**
 * Class for representing a foreign key constraint in SQL
 */
class ForeignKey extends Index {
    protected static $default_args = array(
        array(), array(),
        'ondelete' => 'restrict',
        'onupdate' => 'restrict');

    protected $sources;


    /**
     * Get the table the key references
     *
     * @return Table
     */
    public function getSourceTable() {
        $this->resolve();
        return $this->sources[0]->getTable();
    }


    /**
     * Get the columns the key references
     *
     * @return array
     */
    public function listSources() {
        $this->resolve();
        return $this->sources;
    }


    protected function resolve() {
        if ($this->columns) return;

        parent::resolve();

        if (!isset($this->args[1]) || count($this->args[0]) != count($this->args[1])) {
            throw new \Exception("ForeignKey received the wrong number of sources.");
        }

        foreach($this->args[1] as $source) {
            $this->sources[] = is_string($source)
                ? Column::find($source, $this->table)
                : $source;
        }
    }
}
