<?php

namespace Alchemy\util\promise;


/**
 * Abstraction for objects which can be checked (non-blocking) or waited upon
 * to resolve (blocking, with optional timeout).
 */
class Waitable {
    private $resultType = null;
    protected $result = null;

    protected $started = null;
    protected $timeout = null;
    protected $spin = 10;


    public function __construct($type = null) {
        $this->resultType = $type;
    }


    /**
     * Immediately return the value of the Waitable, resolved or not
     */
    public function __invoke() {
        $this->check();
        return $this->result;
    }


    /**
     * Returns whether or not the Waitable is resolved.
     */
    public function check() {
        $this->precheck();

        return $this->result !== null;
    }


    protected function precheck() {}


    public function resolve($result) {
        if ($result === null
            || $result instanceof \Exception
            || $result instanceof Waitable) {
            $this->result = $result;
        } elseif ($this->resultType && !($result instanceof $this->resultType)) {
            $this->result = new TypeException("Expected {$this->resultType}, got " . get_class($result));
        } else {
            $this->result = $result;
        }

        return $this;
    }


    /**
     * Sets the timeout for this Waitable.
     * $obj->timeout() will cause the Waitable to not wait at all.
     *
     * @param  integer $ms      in milliseconds. 0 = immediately, null = never
     * @param  integer $spin delay between spinlock checks
     * @param  boolean $reset    whether or not to reset the timer
     */
    public function timeout($ms = 0, $spin = 10, $reset = false) {
        if ($reset || $this->started === null) {
            $this->started = microtime(true);
        }

        $this->timeout = ($ms === null) ? null : $this->started + $ms;
        $this->spin = $spin;

        return $this;
    }

    public function type() {
        return $this->resultType;
    }


    /**
     * Blocks until either the Waitable resolves or times out.
     *
     * @return $result resolved value, Exception, or TimeoutException if timed out
     */
    public function wait() {
        while(!$this->check() && ($this->timeout && (microtime(true) < $this->timeout))) {
            usleep($this->spin * 1000);
        }

        if ($this->result === null) {
            $this->result = new TimeoutException();
        }

        return $this->result;
    }


    /**
     * Same as wait(), except it throws Exceptions instead of returning them.
     *
     * @return $result resolved value
     */
    public function expect() {
        if ($this->result === null) {
            $this->wait();
        }

        if ($this->result instanceof \Exception) {
            throw $this->result;
        }

        return $this->result;
    }
}

class SerializableException extends \Exception {
    public function __sleep() {
        return array('message', 'code', 'file', 'line');
    }
}

class TypeException extends SerializableException {}
class TimeoutException extends SerializableException {}