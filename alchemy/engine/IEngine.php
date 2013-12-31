<?php

namespace Alchemy\engine;
use Alchemy\expression\IQuery;


interface IEngine {

    public function beginTransaction();
    public function commitTransaction();
    public function query(IQuery $query);
    public function execute($sql);
    public function rollbackTransaction();
}
