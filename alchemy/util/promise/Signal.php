<?php

namespace Alchemy\util\promise;


/**
 * Signals are Waitables with the concept of a 'source', a dynamic value
 * represented by a callable that returns either an unresolved NULL, an
 * Exception, or a non-NULL value.
 */
class Signal extends Waitable {
    protected $fnSource = null;


    public function __construct($source = null, $type = null) {
        parent::__construct($type);

        if (is_callable($source)) {
            $this->fnSource = $source;
        } else {
            $this->resolve($source);
        }
    }


    /**
     * Queries the Signal's source for its current value. If this is
     * itself a Waitable, that Waitable becomes the new source.
     */
    protected function precheck() {
        if ($this->fnSource !== null) {
            $this->resolve(call_user_func($this->fnSource));
        }

        if ($this->result instanceof Waitable) {
            $this->fnSource = $this->result;
            $this->result = null;
            $this->check();
        }
    }
}