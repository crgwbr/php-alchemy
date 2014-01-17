<?php

namespace Alchemy\util\promise;


/**
 * An interface for objects that are functionally identical when composed
 * with Promises; in other words, they must be immutable and time-independent.
 */
interface IPromisable {

    /**
     * Returns an array mapping promisable methods to return types.
     * A promisable method is one that is dependent only on immutable inputs
     * and is guaranteed to return an instance of a certain type.
     *
     * @return array(method_name => class_name, ...)
     */
    public static function list_promisable_methods();
}