<?php

namespace Alchemy\util\promise;


/**
 * Similar to a Signal, except it only allows a value to be resolved once.
 * Encapsulates an unresolved value and a promise to resolve it later.
 * Use it to simplify communication between asyncronous processes.
 */
class Promise extends Signal {

    protected $onResolvePromises = array();

    public function __get($name) {
        return $this->expect()->{$name};
    }

    public function __set($name, $mixValue) {
        return $this->expect()->{$name} = $mixValue;
    }

    public function __call($name, $aArgs) {
        return call_user_func_array(array($this->expect(), $name), $aArgs);
    }

    protected function precheck() {
        if ($this->result === null) {
            parent::precheck();
        }
    }

    public function resolve($result) {
        if ($this->result === null) {
            parent::resolve($result);

            if ($this->result !== null) {
                foreach($this->onResolvePromises as $promise) {
                    $promise->check();
                }
            }
        }

        return $this;
    }


    /**
     * Returns a Promise of a callback on the results of this Promise.
     * If that in turn returns a Promise, it will wait on that Promise as well.
     * Use this to chain asyncronous callbacks.
     */
    public function then($fnThen = null, $fnFail = null) {
        $promise = new static(new SignalFn_Then($this, $fnThen, $fnFail));

        $this->onResolvePromises[] = $promise;
        return $promise;
    }
}

/**
 * This is a class instead of a lamda function so that it can potentially
 * be serialized, though that also requires that the callbacks and parent
 * Promises be serializable as well.
 */
class SignalFn_Then {
    protected $fnSource;
    protected $fnThen;
    protected $fnFail;

    public function __construct($fnSource, $fnThen = null, $fnFail = null) {
        $this->fnSource = $fnSource;
        $this->fnThen = $fnThen;
        $this->fnFail = $fnFail;
    }

    public function __invoke() {
        $fnSource = $this->fnSource;
        $result = $fnSource();

        if ($result === null) {
            return null;
        }

        $fnNext = ($result instanceof \Exception) ? $this->fnFail : $this->fnThen;
        return $fnNext ? $fnNext($result) : $result;
    }
}