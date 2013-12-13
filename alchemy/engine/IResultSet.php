<?php

namespace Alchemy\engine;
use PDOStatement;
use Iterator;


interface IResultSet extends Iterator {

    public function rowCount();
}