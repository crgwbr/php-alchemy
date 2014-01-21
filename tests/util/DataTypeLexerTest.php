<?php

namespace Alchemy\tests;
use Alchemy\util\DataTypeLexer;


class DataTypeLexerTest extends BaseTest {

    public function testLexer() {
        $type = new DataTypeLexer("String");
        $this->assertEquals("String", $type->getType());
        $this->assertEquals(array(), $type->getArgs());

        $type = new DataTypeLexer("String()");
        $this->assertEquals("String", $type->getType());
        $this->assertEquals(array(), $type->getArgs());

        $type = new DataTypeLexer("Integer(11)");
        $this->assertEquals("Integer", $type->getType());
        $this->assertEquals(array(11), $type->getArgs());

        $type = new DataTypeLexer("Integer(11, primary_key = true)");
        $this->assertEquals("Integer", $type->getType());
        $this->assertEquals(array(11, 'primary_key' => true), $type->getArgs());

        $type = new DataTypeLexer("Integer(10, foreign_key='Table.ColumnID')");
        $this->assertEquals("Integer", $type->getType());
        $this->assertEquals(array(10, 'foreign_key' => 'Table.ColumnID'), $type->getArgs());

        $type = new DataTypeLexer("ForeignKey([.Key1, .Key2], [Foreign.PK1, Foreign.PK2], ondelete = cascade)");
        $this->assertEquals("ForeignKey", $type->getType());
        $this->assertEquals(array(array('.Key1', '.Key2'), array('Foreign.PK1', 'Foreign.PK2'), 'ondelete' => 'cascade'), $type->getArgs());
    }
}
