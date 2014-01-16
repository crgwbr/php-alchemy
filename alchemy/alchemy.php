<?php
/**
 * PHPAlchemy
 *
 * Include this file in your application to start using PHP's best ORM
 */

// Update include path so that the following includes work
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

// Utils
require_once "util/DataTypeLexer.php";
require_once "util/Monad.php";
require_once "util/promise/Waitable.php";
require_once "util/promise/Signal.php";
require_once "util/promise/Promise.php";

// Interfaces
require_once "expression/IQueryFragment.php";
require_once "expression/IQueryValue.php";
require_once "expression/IQuery.php";

// Basic expressions
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
require_once "expression/Datetime.php";
require_once "expression/Timestamp.php";
require_once "expression/Date.php";
require_once "expression/Time.php";
require_once "expression/Bool.php";
require_once "expression/Decimal.php";
require_once "expression/TinyInt.php";
require_once "expression/SmallInt.php";
require_once "expression/MediumInt.php";
require_once "expression/BigInt.php";
require_once "expression/Char.php";
require_once "expression/Blob.php";
require_once "expression/Float.php";
require_once "expression/Binary.php";

// Query Structure
require_once "expression/Join.php";

// Queries
require_once "expression/Query.php";
require_once "expression/Select.php";
require_once "expression/Insert.php";
require_once "expression/Update.php";
require_once "expression/Delete.php";

// DDL
require_once "expression/DDLQuery.php";
require_once "expression/Create.php";
require_once "expression/Drop.php";

// Dialects
require_once "dialect/Compiler.php";
require_once "dialect/ANSICompiler.php";
require_once "dialect/SQLiteCompiler.php";
require_once "dialect/MySQLCompiler.php";

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
