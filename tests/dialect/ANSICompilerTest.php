<?php

namespace Alchemy\tests;

use Alchemy\dialect\ANSICompiler;
use Alchemy\expression as expr;
use Alchemy\expression\Column;
use Alchemy\expression\Predicate;


class ANSICompilerTest extends BaseTest {

    public function testBigInt() {
        $ansi = new ANSICompiler();
        $col = Column::BigInt(array(20, 'null' => false, 'auto_increment' => false), null, 'Col');

        $this->assertEquals("Col BIGINT(20) NOT NULL",
            $ansi->Create_Column($col));
    }


    public function testBinary() {
        $ansi = new ANSICompiler();
        $col = Column::Binary(array(100, 'null' => false), null, 'Col');

        $this->assertEquals("Col BINARY(100) NOT NULL", $ansi->Create_Column($col));
    }


    public function testPredicate() {
        $ansi = new ANSICompiler();

        $exprA = Predicate::lt(new expr\Scalar(3), new expr\Scalar(5));
        $this->assertEquals(":p0 < :p1", $ansi->compile($exprA));

        $col = Column::Integer(null, null, 'Col');
        $exprB = Predicate::isNull($col);
        $this->assertEquals("NOT (Col IS NULL)", $ansi->compile($exprB->not()));

        $exprC = Predicate::in($col, new expr\Scalar(3), new expr\Scalar(5));
        $this->assertEquals("Col IN (:p2, :p3)", $ansi->compile($exprC));

        $exprD = Predicate::and_($exprA->not(), $exprB, $exprC);
        $this->assertEquals("NOT ((NOT (:p0 < :p1) AND Col IS NULL AND Col IN (:p2, :p3)))", $ansi->compile($exprD->not()));
    }


    public function testBlob() {
        $ansi = new ANSICompiler();
        $col = Column::Blob(null, null, 'Col');

        $this->assertEquals("Col BLOB NOT NULL", $ansi->Create_Column($col));
    }


    public function testBool() {
        $ansi = new ANSICompiler();
        $col = Column::Bool(array(11, 'null' => false), null, 'Col');

        $this->assertEquals("Col", $ansi->compile($col));
        $this->assertEquals("Col BOOL NOT NULL",
            $ansi->Create_Column($col));
    }


    public function testColumnRef() {
        $ansi = new ANSICompiler();
        $table = new expr\Table('Tbl', array('Col' => Column::Bool()));
        $table = $table->getRef();

        $this->assertEquals("tb1.Col",
            $ansi->compile($table->Col, array('alias_tables' => true)));
        $this->assertEquals("Col as Col",
            $ansi->compile($table->Col, array('alias_columns' => true)));
    }


    public function testChar() {
        $ansi = new ANSICompiler();
        $col = Column::Char(array(200, 'null' => false), null, 'Col');

        $this->assertEquals("Col CHAR(200) NOT NULL", $ansi->Create_Column($col));
    }


    public function testCreate() {
        $ansi = new ANSICompiler();
        $expr = new expr\Create(
            new expr\Table('Tbl', array(
                'Col' => Column::Integer(array(11, 'null' => false, 'auto_increment' => false)),
                'Key' => Column::Foreign(array('self.Col', 'null' => true)) )));

        $this->assertEquals(
            "CREATE TABLE IF NOT EXISTS Tbl (Col INT(11) NOT NULL, Key INT(11) NULL, FOREIGN KEY (Key) REFERENCES Tbl (Col))",
            $ansi->compile($expr));
    }


    public function testDate() {
        $ansi = new ANSICompiler();
        $col = Column::Date(array('null' => false), null, 'Col');

        $this->assertEquals("Col DATE NOT NULL", $ansi->Create_Column($col));
    }


    public function testDatetime() {
        $ansi = new ANSICompiler();
        $col = Column::Datetime(array('null' => true), null, 'Col');

        $this->assertEquals("Col DATETIME NULL", $ansi->Create_Column($col));
    }


    public function testDecimal() {
        $ansi = new ANSICompiler();
        $col = Column::Decimal(array(5, 3), null, 'Col');

        $this->assertEquals("Col DECIMAL(5, 3) NOT NULL",
            $ansi->Create_Column($col));
    }


    public function testDrop() {
        $ansi = new ANSICompiler();
        $expr = new expr\Drop(new expr\Table('Tbl', array()));

        $this->assertEquals("DROP TABLE IF EXISTS Tbl", $ansi->compile($expr));
    }


    public function testFloat() {
        $ansi = new ANSICompiler();
        $col = Column::Float(array(23, 'null' => false), null, 'Col');

        $this->assertEquals("Col FLOAT(23) NOT NULL",
            $ansi->Create_Column($col));
    }


    public function testInteger() {
        $ansi = new ANSICompiler();
        $col = Column::Integer(array(11, 'null' => false, 'auto_increment' => false), null, 'Col');

        $this->assertEquals("Col INT(11) NOT NULL",
            $ansi->Create_Column($col));
    }


    public function testJoin() {
        $ansi = new ANSICompiler();
        $table = new expr\Table('Tbl', array(
            'Col' => Column::Bool() ));
        $table = $table->getRef();

        $expr = Predicate::lt($table->Col, $table->Col);
        $join = new expr\Join(expr\Join::LEFT, expr\Join::INNER, $table, $expr);

        $this->assertEquals("LEFT INNER JOIN Tbl ON Col < Col", $ansi->compile($join));
    }


    public function testMediumInt() {
        $ansi = new ANSICompiler();
        $col = Column::MediumInt(array(8, 'null' => false, 'auto_increment' => false), null, 'Col');

        $this->assertEquals("Col MEDIUMINT(8) NOT NULL",
            $ansi->Create_Column($col));
    }


    public function testScalar() {
        $ansi = new ANSICompiler();
        $scalar = new expr\Scalar(3);

        $this->assertEquals(":p0", $ansi->compile($scalar));
    }


    public function testSmallInt() {
        $ansi = new ANSICompiler();
        $col = Column::SmallInt(array(6, 'null' => false, 'auto_increment' => false), null, 'Col');

        $this->assertEquals("Col SMALLINT(6) NOT NULL",
            $ansi->Create_Column($col));
    }


    public function testString() {
        $ansi = new ANSICompiler();
        $col = Column::String(array(200, 'null' => false), null, 'Col');

        $this->assertEquals("Col VARCHAR(200) NOT NULL", $ansi->Create_Column($col));
    }


    public function testTableRef() {
        $ansi = new ANSICompiler();
        $table = new expr\Table('Tbl', array());
        $table = $table->getRef();

        $this->assertEquals("Tbl", $table->name());
        $this->assertEquals("Tbl tb1", $ansi->compile($table, array('alias_tables' => true)));
    }


    public function testTime() {
        $ansi = new ANSICompiler();
        $col = Column::Time(array('null' => false), null, 'Col');

        $this->assertEquals("Col TIME NOT NULL", $ansi->Create_Column($col));
    }


    public function testTimestamp() {
        $ansi = new ANSICompiler();
        $col = Column::Timestamp(array('null' => true), null, 'Col');

        $this->assertEquals("Col TIMESTAMP NULL", $ansi->Create_Column($col));
    }


    public function testTinyInt() {
        $ansi = new ANSICompiler();
        $col = Column::TinyInt(array(4, 'null' => false, 'auto_increment' => false), null, 'Col');

        $this->assertEquals("Col TINYINT(4) NOT NULL",
            $ansi->Create_Column($col));
    }
}
