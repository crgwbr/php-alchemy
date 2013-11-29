<?php

namespace Alchemy\expression;
use PDO;


class Null extends Scalar {
    protected static $data_type = PDO::PARAM_NULL;
}
