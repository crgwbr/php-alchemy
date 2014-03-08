<?php

namespace Alchemy\util\promise;


/**
 * Similar to a Signal, except it only allows a value to be resolved once.
 * Encapsulates an unresolved value and a promise to resolve it later.
 * Use it to simplify communication between asyncronous processes.
 */
class Promise extends Signal {

    protected $onResolvePromises = array();


    /**
     * Return a single promise that is resolved when the given array
     * of promise are all resolved.
     *
     * @param array $promises
     * @return Promise
     */
    public static function when(array $promises = array()) {
        $results = array();
        $when = new Promise();

        $wait = function() use (&$promises, &$results, &$when, &$wait) {
            if (count($promises) == 0) {
                $when->resolve($results);
                return;
            }

            $p = array_pop($promises);
            $p->then(function($r) use (&$results, &$wait) {
                $results[] = $r;
                $wait();
            });
        };

        $wait();
        return $when;
    }


    public function __get($name) {
        return $this->__call('__get', array($name));
    }


    /**
     * If possible, return a Promise to call the method later,
     * else just call the method.
     */
    public function __call($name, $args) {
        $type = self::get_return_type($this->type(), $name);

        if ($type) {
            return $this->then(function($obj) use ($name, $args) {
                return call_user_func_array(array($obj, $name), $args);
            }, null, $type, false);
        }

        return $name == '__get'
            ? $this->expect()->{$args[0]}
            : call_user_func_array(array($this->expect(), $name), $args);
    }


    /**
     * Wait for the Promise to resolve and cast it to a string.
     */
    public function __tostring() {
        return (string) $this->wait();
    }


    /**
     * Get the return type of a given method on an IPromisable class, if known.
     *
     * @param  string $cls    class name
     * @param  string $method method name
     * @return string|null    return type
     */
    public static function get_return_type($cls, $method) {
        static $return_types = array();

        if (isset($return_types[$cls][$method])) {
            return $return_types[$cls][$method];
        }

        if (isset($return_types[$cls]) || !$cls || !$method) {
            return false;
        }

        $return_types[$cls] = method_exists($cls, "list_promisable_methods")
            ? $cls::list_promisable_methods()
            : false;

        return isset($return_types[$cls][$method]) ? $return_types[$cls][$method] : false;
    }


    /**
     * Subclasses override this to do something during check()
     */
    protected function precheck() {
        if ($this->result === null) {
            parent::precheck();
        }
    }


    /**
     * A Promise will only resolve once. Further resolve()s will be ignored.
     *
     * @param  mixed $result
     * @return this
     */
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
    public function then($fnThen = null, $fnFail = null, $type = null, $check = true) {
        $promise = new static(new SignalFn_Then($this, $fnThen, $fnFail), $type);

        if (is_null($this->result)) {
            $this->onResolvePromises[] = $promise;
        } elseif ($check) {
            $promise->check();
        }

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
        $result = call_user_func($this->fnSource);

        if ($result === null) {
            return null;
        }

        $fnNext = ($result instanceof \Exception) ? $this->fnFail : $this->fnThen;
        return $fnNext ? $fnNext($result) : $result;
    }
}
