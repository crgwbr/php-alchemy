<?php

namespace Alchemy\expression;


/**
 * Interface for query like objects
 */
interface IQuery {

    /**
     * Recursively get all scalar parameters used by this
     * query IN THE ORDER THEY ARE USED in the query
     *
     * @return array(Scalar, Scalar, ...)
     */
    public function getParameters();
}
