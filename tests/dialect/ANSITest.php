<?php

namespace Alchemy\tests;
use Alchemy\dialect\ANSI_BinaryExpression;
use Alchemy\dialect\ANSI_Bool;
use Alchemy\dialect\ANSI_Create;
use Alchemy\dialect\ANSI_Drop;
use Alchemy\dialect\ANSI_CompoundExpression;
use Alchemy\dialect\ANSI_InclusiveExpression;
use Alchemy\dialect\ANSI_Insert;
use Alchemy\dialect\ANSI_Integer;
use Alchemy\dialect\ANSI_Join;
use Alchemy\dialect\ANSI_Operator;
use Alchemy\dialect\ANSI_Scalar;
use Alchemy\dialect\ANSI_Select;
use Alchemy\dialect\ANSI_String;
use Alchemy\dialect\ANSI_Table;
use Alchemy\dialect\ANSI_Timestamp;


class ANSITest extends BaseTest {

    public function testBinaryExpression() {
        $expr = new ANSI_BinaryExpression(array(
            'left' => 'LEFT',
            'operator' => '<>',
            'right' => 'RIGHT',
        ));

        $this->assertEquals("LEFT <> RIGHT", (string)$expr);
    }


    public function testBool() {
        $col = new ANSI_Bool(array(
            'tableAlias' => 'TALIAS',
            'name' => 'COL',
            'alias' => 'ALIAS',
            'args' => array(11),
            'kwargs' => array('null' => false),
        ));

        $this->assertEquals("TALIAS.COL", (string)$col);
        $this->assertEquals("COL", $col->getName());
        $this->assertEquals("COL BOOL NOT NULL", $col->definition());
        $this->assertEquals("TALIAS.COL as ALIAS", $col->getSelectDeclaration());
    }


    public function testCompoundExpression() {
        $expr = new ANSI_CompoundExpression(array(
            'components' => array(1, 2, 3),
        ));

        $this->assertEquals("(1 2 3)", (string)$expr);
    }


    public function testCreate() {
        $expr = new ANSI_Create(array(
            'table' => new ANSI_Table(array(
                'name' => 'TNAME',
                'alias' => 'TALIAS',
                'columns' => array(new ANSI_Integer(array(
                    'name' => 'COL',
                    'args' => array(11),
                    'kwargs' => array('null' => false, 'auto_increment' => false),
                )))
            ))
        ));

        $this->assertEquals(
            "CREATE TABLE IF NOT EXISTS TNAME (COL INT(11) NOT NULL)",
            (string)$expr
        );
    }


    public function testDrop() {
        $expr = new ANSI_Drop(array(
            'table' => new ANSI_Table(array(
                'name' => 'TNAME'
            ))
        ));

        $this->assertEquals("DROP TABLE IF EXISTS TNAME", (string)$expr);
    }


    public function testInclusiveExpression() {
        $expr = new ANSI_InclusiveExpression(array(
            'left' => 'L1',
            'in' => array(1, 2, 3),
        ));

        $this->assertEquals("L1 IN (1, 2, 3)", (string)$expr);
    }


    public function testInteger() {
        $col = new ANSI_Integer(array(
            'tableAlias' => 'TALIAS',
            'name' => 'COL',
            'alias' => 'ALIAS',
            'args' => array(11),
            'kwargs' => array('null' => false, 'auto_increment' => false),
        ));

        $this->assertEquals("TALIAS.COL", (string)$col);
        $this->assertEquals("COL", $col->getName());
        $this->assertEquals("COL INT(11) NOT NULL", $col->definition());
        $this->assertEquals("TALIAS.COL as ALIAS", $col->getSelectDeclaration());
    }


    public function testJoin() {
        $join = new ANSI_Join(array(
            'direction' => 'LEFT',
            'type' => 'INNER',
            'table' => 'TNAME',
            'on' => 'CONDITION',
        ));

        $this->assertEquals("LEFT INNER JOIN TNAME ON CONDITION", (string)$join);
    }


    public function testOperator() {
        $oper = new ANSI_Operator(array(
            'type' => '=',
        ));

        $this->assertEquals("=", (string)$oper);
    }


    public function testScalar() {
        $scalar = new ANSI_Scalar(array('name' => 'p5'));

        $this->assertEquals(":p5", (string)$scalar);
    }


    public function testString() {
        $col = new ANSI_String(array(
            'tableAlias' => 'TALIAS',
            'name' => 'COL',
            'alias' => 'ALIAS',
            'args' => array(200),
            'kwargs' => array('null' => false),
        ));

        $this->assertEquals("TALIAS.COL", (string)$col);
        $this->assertEquals("COL", $col->getName());
        $this->assertEquals("COL VARCHAR(200) NOT NULL", $col->definition());
        $this->assertEquals("TALIAS.COL as ALIAS", $col->getSelectDeclaration());
    }


    public function testTable() {
        $table = new ANSI_Table(array(
            'name' => 'TNAME',
            'alias' => 'TALIAS',
        ));

        $this->assertEquals("TNAME TALIAS", (string)$table);
        $this->assertEquals("TNAME", $table->getName());
        $this->assertEquals("TALIAS", $table->getAlias());
    }


    public function testTimestamp() {
        $col = new ANSI_Timestamp(array(
            'tableAlias' => 'TALIAS',
            'name' => 'COL',
            'alias' => 'ALIAS',
            'args' => array(),
            'kwargs' => array('null' => true),
        ));

        $this->assertEquals("TALIAS.COL", (string)$col);
        $this->assertEquals("COL", $col->getName());
        $this->assertEquals("COL TIMESTAMP NULL", $col->definition());
        $this->assertEquals("TALIAS.COL as ALIAS", $col->getSelectDeclaration());
    }
}
