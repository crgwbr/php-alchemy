<?php

namespace Alchemy\orm;
use Alchemy\core\query\Predicate;
use Alchemy\core\query\Query;
use Alchemy\core\query\TableRef;
use Alchemy\util\promise\Promise;


/**
 * Represents a table reference through a singular relationship
 */
class ORMTableRef extends TableRef {

    protected $predicate;
    protected $relcache = array();


    //public function __call($name, $args);


    public function __construct($schema, $predicate = null) {
        parent::__construct($schema);
        $this->predicate = $predicate;
    }


    public function __get($name) {
        if (array_key_exists($name, $this->relcache)) {
            return $this->relcache[$name];
        }

        if ($this->schema->hasRelationship($name)) {
            $rel = $this->schema->getRelationship($name)->getRef($this);
            $this->relcache[$name] = $rel;
            return $rel;
        }

        return parent::__get($name);
    }


    public function all(Predicate $expr) {}


    public function getDescription($maxdepth = 3, $curdepth = 0) {
        $str = parent::getDescription($maxdepth, $curdepth);
        $prd = $this->predicate ? "{$this->predicate->getDescription($maxdepth, $curdepth)}" : "";
        return "$str {$prd}";
    }


    public function none(Predicate $expr) {}


    public function predicate() {
        if ($this->predicate instanceof Promise) {
            $this->predicate = $this->predicate->expect();
        }

        return $this->predicate;
    }


    public function relationships() {
        $relationships = array();
        foreach($this->schema->listRelationships() as $name => $prop) {
            $relationships[] = $this->{$name};
        }

        return $relationships;
    }


    public function where() {
        return Query::ORM($this)->where(func_get_args());
    }
}
