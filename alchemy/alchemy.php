<?php

// Update include path so that the following includes work
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

// Utils
require_once "util/DataTypeLexer.php";
require_once "util/Monad.php";
require_once "util/Promise.php";

// Basic expressions
require_once "expression/Value.php";
require_once "expression/Scalar.php";
require_once "expression/Operator.php";

// Schema definition
require_once "expression/Column.php";
require_once "expression/Table.php";

// Expressions
require_once "expression/Expression.php";
require_once "expression/BinaryExpression.php";
require_once "expression/InclusiveExpression.php";
require_once "expression/CompoundExpression.php";

// Data types
require_once "expression/String.php";
require_once "expression/Integer.php";
require_once "expression/Timestamp.php";
require_once "expression/Bool.php";

// Query Structure
require_once "expression/Join.php";

// Queries
require_once "expression/IQuery.php";
require_once "expression/Query.php";
require_once "expression/Select.php";
require_once "expression/Insert.php";
require_once "expression/Update.php";

// DDL
require_once "expression/DDLQuery.php";
require_once "expression/Create.php";
require_once "expression/Drop.php";

// Dialects
require_once "dialect/DialectTranslator.php";
require_once "dialect/ANSI.php";
require_once "dialect/SQLite.php";
require_once "dialect/MySQL.php";

// Engines
require_once "engine/IResultSet.php";
require_once "engine/IEngine.php";
require_once "engine/ResultSet.php";
require_once "engine/Engine.php";

// ORM Proper
require_once "orm/DDL.php";
require_once "orm/WorkQueue.php";
require_once "orm/Session.php";
require_once "orm/SessionSelect.php";
require_once "orm/DataMapper.php";
