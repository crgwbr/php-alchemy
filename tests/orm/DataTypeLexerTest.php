<?php

namespace Alchemy\tests;
use Alchemy\orm\DataTypeLexer;


class DataTypeLexerTest extends BaseTest {

    public function testLexer() {
        $type = new DataTypeLexer("String");
        $this->assertEquals("String", $type->getType());
        $this->assertEquals(array(), $type->getArgs());
        $this->assertEquals(array(), $type->getKeywordArgs());

        $type = new DataTypeLexer("String()");
        $this->assertEquals("String", $type->getType());
        $this->assertEquals(array(), $type->getArgs());
        $this->assertEquals(array(), $type->getKeywordArgs());

        $type = new DataTypeLexer("Integer(11)");
        $this->assertEquals("Integer", $type->getType());
        $this->assertEquals(array(11), $type->getArgs());
        $this->assertEquals(array(), $type->getKeywordArgs());

        $type = new DataTypeLexer("Integer(11, primary_key = true)");
        $this->assertEquals("Integer", $type->getType());
        $this->assertEquals(array(11), $type->getArgs());
        $this->assertEquals(array('primary_key' => true), $type->getKeywordArgs());

        $type = new DataTypeLexer("Integer(10, foreign_key='Table.ColumnID')");
        $this->assertEquals("Integer", $type->getType());
        $this->assertEquals(array(10), $type->getArgs());
        $this->assertEquals(array('foreign_key' => 'Table.ColumnID'), $type->getKeywordArgs());
    }
}
