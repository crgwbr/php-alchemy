<?php

namespace Alchemy\tests;

use Alchemy\dialect\ANSICompiler;
use Alchemy\expression as expr;


class ANSICompilerTest extends BaseTest {

    public function testBinaryExpression() {
        $ansi = new ANSICompiler();
        $expr = new expr\BinaryExpression(new expr\Scalar(3), expr\Operator::lt(), new expr\Scalar(5));

        $this->assertEquals(":p0 < :p1", $ansi->compile($expr));
    }


    public function testBool() {
        $ansi = new ANSICompiler();
        $col = new expr\Bool(array(11, 'null' => false));
        $col->assign(new expr\Table('Tbl', array()), 'Col', 'Alias');

        $this->assertEquals("Col", $ansi->compile($col));
        $this->assertEquals("tb1.Col",
            $ansi->compile($col, array('alias_tables' => true)));
        $this->assertEquals("Col as Alias",
            $ansi->compile($col, array('alias_columns' => true)));
        $this->assertEquals("Col BOOL NOT NULL",
            $ansi->Create_Column($col));
    }


    public function testCreate() {
        $ansi = new ANSICompiler();
        $expr = new expr\Create(
            new expr\Table('Tbl', array(
                'Col' => new expr\Integer(array(11, 'null' => false, 'auto_increment' => false)) )));

        $this->assertEquals(
            "CREATE TABLE IF NOT EXISTS Tbl (Col INT(11) NOT NULL)",
            $ansi->compile($expr));
    }


    public function testDecimal() {
        $ansi = new ANSICompiler();
        $col = new expr\Decimal(array(5, 3));
        $col->assign(null, 'Col');

        $this->assertEquals("Col Decimal(5, 3) NOT NULL",
            $ansi->Create_Column($col));
    }


    public function testDrop() {
        $ansi = new ANSICompiler();
        $expr = new expr\Drop(new expr\Table('Tbl', array()));

        $this->assertEquals("DROP TABLE IF EXISTS Tbl", $ansi->compile($expr));
    }


    public function testCompoundExpression() {
        $ansi = new ANSICompiler();
        $bnxp = new expr\BinaryExpression(new expr\Scalar(3), expr\Operator::lt(), new expr\Scalar(5));
        $expr = new expr\CompoundExpression($bnxp);
        $expr->and($bnxp);

        $this->assertEquals("(:p0 < :p1 AND :p0 < :p1)", $ansi->compile($expr));
    }


    public function testInclusiveExpression() {
        $ansi = new ANSICompiler();
        $expr = new expr\InclusiveExpression(new expr\Scalar(3),
            array(new expr\Scalar(4), new expr\Scalar(5)));

        $this->assertEquals(":p0 IN (:p1, :p2)", $ansi->compile($expr));
    }


    public function testInteger() {
        $ansi = new ANSICompiler();
        $col = new expr\Integer(array(11, 'null' => false, 'auto_increment' => false));
        $col->assign(null, 'Col');

        $this->assertEquals("Col INT(11) NOT NULL",
            $ansi->Create_Column($col));
    }


    public function testJoin() {
        $ansi = new ANSICompiler();
        $table = new expr\Table('Tbl', array(
            'Col' => new expr\Bool() ));
        $expr = new expr\BinaryExpression($table->Col, expr\Operator::lt(), $table->Col);
        $join = new expr\Join(expr\Join::LEFT, expr\Join::INNER, $table, $expr);

        $this->assertEquals("LEFT INNER JOIN Tbl ON Col < Col", $ansi->compile($join));
    }


    public function testOperator() {
        $ansi = new ANSICompiler();
        $oper = expr\Operator::lt();

        $this->assertEquals("<", $ansi->compile($oper));
    }


    public function testScalar() {
        $ansi = new ANSICompiler();
        $scalar = new expr\Scalar(3);

        $this->assertEquals(":p0", $ansi->compile($scalar));
    }


    public function testString() {
        $ansi = new ANSICompiler();
        $col = new expr\String(array(200, 'null' => false));
        $col->assign(null, 'Col');

        $this->assertEquals("Col VARCHAR(200) NOT NULL", $ansi->Create_Column($col));
    }


    public function testTable() {
        $ansi = new ANSICompiler();
        $table = new expr\Table('Tbl', array());

        $this->assertEquals("Tbl", $table->getName());
        $this->assertEquals("Tbl tb1", $ansi->compile($table, array('alias_tables' => true)));
    }


    public function testTimestamp() {
        $ansi = new ANSICompiler();
        $col = new expr\Timestamp(array('null' => true));
        $col->assign(null, 'Col');

        $this->assertEquals("Col TIMESTAMP NULL", $ansi->Create_Column($col));
    }
}
