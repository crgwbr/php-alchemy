<?php

namespace Alchemy\expression;
use PDO;


class String extends Scalar {
    protected static $data_type = PDO::PARAM_STR;
}
