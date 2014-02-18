<?php

namespace Alchemy\core\query;


/**
 * Interface for query fragments
 */
interface IQueryFragment {

    /**
     * Recursively get all scalar parameters used by this query
     *
     * @return array(Scalar, Scalar, ...)
     */
    public function parameters();
}
