<?php

namespace Alchemy\util;


/**
 * Allow you to define deferred callbacks for events
 * that happen at an unknown time in the future, potentially
 * never.
 */
class Promise {
    protected $resolvedHandlers = array();


    /**
     * Register a function to be called when this promise is resolved.
     *
     * @param function $fn
     */
    public function done($fn) {
        $this->resolvedHandlers[] = $fn;
    }


    /**
     * Resolve the promise. Accepts a variable number of arguments to send
     * any registered callbacks.
     */
    public function resolve() {
        $data = func_get_args();

        foreach ($this->resolvedHandlers as $fn) {
            call_user_func_array($fn, $data);
        }
    }
}
