<?php

namespace Alchemy\expression;
use PDO;


class Integer extends Scalar {
    protected static $data_type = PDO::PARAM_INT;
}
