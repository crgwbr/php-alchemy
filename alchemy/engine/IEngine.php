<?php

namespace Alchemy\engine;
use Alchemy\expression\QueryManager;


interface IEngine {

    public function beginTransaction();
    public function commitTransaction();
    public function query(QueryManager $query);
    public function execute($sql);
    public function rollbackTransaction();
}