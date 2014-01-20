<?php

namespace Alchemy\dialect;


/**
 * Interface for dialect-compilable objects
 */
interface ICompilable {

    /**
     * Get the locally-unique element id
     *
     * @return string
     */
    public function getID();


    /**
     * List of compilation roles that this object can play
     *
     * @return array in order of specificity, preference
     */
    public function listRoles();
}