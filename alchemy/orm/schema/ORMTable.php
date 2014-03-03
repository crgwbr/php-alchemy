<?php

namespace Alchemy\orm;
use Alchemy\core\schema\Table;
use Alchemy\util\DataTypeLexer;


/**
 * Defines a table aware of relationships and other abstractions
 */
class ORMTable extends Table {

    protected $relationships = array();


    public function getClass() {
        return $this->args['class'];
    }


    public function getRef() {
        return new ORMTableRef($this);
    }


    public function addRelationship($name, $rel) {
        if (is_string($rel)) {
            $type = new DataTypeLexer($rel);
            $t = $type->getType();
            $rel = Relationship::$t($type->getArgs(), $this, $name);
        }

        $this->args['relationships'][$name] = $rel;
        $this->relationships[$name] = $rel;
    }


    public function getRelationship($name) {
        if (!array_key_exists($name, $this->relationships)) {
            if (array_key_exists($name, $this->args['relationships'])) {
                $this->addRelationship($name, $this->args['relationships'][$name]);
            } else {
                throw new \Exception("Unknown relationship '{$this->name}.{$name}'");
            }
        }

        return $this->relationships[$name];
    }


    public function hasRelationship($name) {
        return array_key_exists($name, $this->args['relationships']);
    }


    public function listRelationships() {
        $this->resolve();
        return $this->relationships;
    }


    protected function resolve() {
        if ($this->resolved) return;
        parent::resolve();

        foreach ($this->args['relationships'] as $name => $rel) {
            $rel = $this->getRelationship($name);
        }
    }
}
