<?php

// Update include path so that the following includes work
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));


require_once "expression/Value.php";
require_once "expression/Scalar.php";

require_once "expression/Bool.php";
require_once "expression/String.php";
require_once "expression/Integer.php";
require_once "expression/Null.php";

require_once "expression/Operator.php";

require_once "expression/Expression.php";
require_once "expression/BinaryExpression.php";
require_once "expression/InclusiveExpression.php";
require_once "expression/CompoundExpression.php";

require_once "expression/Table.php";
require_once "expression/Column.php";
require_once "expression/Join.php";

require_once "expression/Query.php";
require_once "expression/QueryManager.php";
require_once "expression/SelectQuery.php";
require_once "expression/InsertQuery.php";

require_once "engine/IResultSet.php";
require_once "engine/IEngine.php";
require_once "engine/ResultSet.php";
require_once "engine/Engine.php";

require_once "orm/schema/Column.php";
require_once "orm/schema/Integer.php";
require_once "orm/schema/String.php";
require_once "orm/schema/Text.php";
require_once "orm/schema/Timestamp.php";

require_once "orm/ddl/DDL.php";
require_once "orm/ddl/Create.php";
require_once "orm/ddl/Drop.php";

require_once "orm/query/DeferredSelect.php";
require_once "orm/query/DeferredQueryManager.php";

require_once "orm/Session.php";
require_once "orm/DataTypeLexer.php";
require_once "orm/DataMapper.php";
