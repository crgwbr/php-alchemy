<?php

namespace Alchemy\util;


class Promise {
    protected $resolvedHandlers = array();

    public function done($fn) {
        $this->resolvedHandlers[] = $fn;
    }


    public function resolve() {
        $data = func_get_args();

        foreach ($this->resolvedHandlers as $fn) {

            call_user_func_array($fn, $data);
        }
    }
}
