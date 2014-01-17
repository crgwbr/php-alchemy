<?php

namespace Alchemy\expression;
use Alchemy\dialect\ICompilable;


/**
 * Abstract class for query fragments
 */
abstract class QueryElement implements ICompilable {

    /**
     * List of compilation roles that this object can play
     *
     * @return array in order of specificity, preference
     */
    public function listRoles() {
        $roles = array();
        $cls = get_called_class();

        while ($cls) {
            $parts = explode('\\', $cls);
            $roles[] = array_pop($parts);
            $cls = get_parent_class($cls);
        }

        return $roles;
    }
}
