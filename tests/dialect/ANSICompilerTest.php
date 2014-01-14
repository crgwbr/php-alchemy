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
        $col = new expr\Bool('Ta', 'Col', 'Alias', array(11), array('null' => false));

        $this->assertEquals("Col", $ansi->compile($col));
        $this->assertEquals("Ta.Col",
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
                'Col' => new expr\Integer('t', 'Col', 'a', array(11), array('null' => false, 'auto_increment' => false)) )));

        $this->assertEquals(
            "CREATE TABLE IF NOT EXISTS Tbl (Col INT(11) NOT NULL)",
            $ansi->compile($expr));
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
        $col = new expr\Integer('Ta', 'Col', 'Alias', array(11),
            array('null' => false, 'auto_increment' => false));

        $this->assertEquals("Col INT(11) NOT NULL",
            $ansi->Create_Column($col));
    }


    public function testJoin() {
        $ansi = new ANSICompiler();
        $table = new expr\Table('Tbl', array(
            'Col' => new expr\Bool('t', 'Col', 'a', array(), array()) ));
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
        $col = new expr\String('Ta', 'Col', 'Alias', array(200), array('null' => false));

        $this->assertEquals("Col VARCHAR(200) NOT NULL", $ansi->Create_Column($col));
    }


    public function testTable() {
        $ansi = new ANSICompiler();
        $table = new expr\Table('Tbl', array());

        $this->assertEquals("Tbl", $table->getName());
        $this->assertEquals("tb1", $table->getAlias());
        $this->assertEquals("Tbl tb1", $ansi->compile($table, array('alias_tables' => true)));
    }


    public function testTimestamp() {
        $ansi = new ANSICompiler();
        $col = new expr\Timestamp('Ta', 'Col', 'Alias', array(), array('null' => true));

        $this->assertEquals("Col TIMESTAMP NULL", $ansi->Create_Column($col));
    }
}
