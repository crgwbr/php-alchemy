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
require_once "util/promise/IPromisable.php";

// Dialects
require_once "dialect/Compiler.php";
require_once "dialect/ANSICompiler.php";
require_once "dialect/SQLiteCompiler.php";
require_once "dialect/MySQLCompiler.php";

// Core
require_once "core/IElement.php";
require_once "core/Element.php";

// Query Elements
require_once "core/query/IQueryFragment.php";
require_once "core/query/IQueryValue.php";
require_once "core/query/IQuery.php";
require_once "core/query/ColumnRef.php";
require_once "core/query/TableRef.php";
require_once "core/query/Scalar.php";
require_once "core/query/Expression.php";
require_once "core/query/Predicate.php";

// Queries
require_once "core/query/Query.php";
require_once "core/query/Insert.php";
require_once "core/query/Join.php";
require_once "core/query/DDLQuery.php";
require_once "core/query/query-elements.php";

// Schema
require_once "core/schema/Table.php";
require_once "core/schema/TableElement.php";
require_once "core/schema/Column.php";
require_once "core/schema/Index.php";
require_once "core/schema/Foreign.php";
require_once "core/schema/ForeignKey.php";
require_once "core/schema/schema-elements.php";

// Engines
require_once "engine/IResultSet.php";
require_once "engine/IEngine.php";
require_once "engine/ResultSet.php";
require_once "engine/Engine.php";

// ORM Queries
require_once "orm/query/ORMQuery.php";
require_once "orm/query/ORMTableRef.php";

// ORM Schema
require_once "orm/schema/Relationship.php";
require_once "orm/schema/ORMTable.php";
require_once "orm/schema/OneToMany.php";
require_once "orm/schema/ManyToOne.php";
require_once "orm/schema/OneToOne.php";
require_once "orm/orm-elements.php";

// ORM Proper
require_once "orm/DDL.php";
require_once "orm/WorkQueue.php";
require_once "orm/Session.php";
require_once "orm/SessionSelect.php";
require_once "orm/RelatedSet.php";
require_once "orm/DataMapper.php";
