<?php

namespace Alchemy\expression;


/**
 * Class for representing an index in SQL
 */
class Index extends QueryElement {
    protected $name = "";
    protected $columns = array();


    /**
     * Object Constructor
     *
     * @param array $args
     */
    public function __construct($name, array $columns = array()) {
        $this->name = $name;
        $this->columns = $columns;
    }


    /**
     * Get the index name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }


    /**
     * List the columns used by this index
     *
     * @return array
     */
    public function listColumns() {
        return $this->columns;
    }
}
