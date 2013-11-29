<?php

namespace Alchemy\expression;
use PDO;


class Bool extends Scalar {
    protected static $data_type = PDO::PARAM_BOOL;
}
