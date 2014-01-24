<?php

namespace Alchemy\tests;

use Alchemy\expression\Element;


class ElementTest extends BaseTest {

    public function testIDsDistinct() {
        $objA = new Element();
        $objB = new Element();

        $this->assertEquals($objA->getID(), $objA->getID());
        $this->assertEquals($objB->getID(), $objB->getID());
        $this->assertNotEquals($objA->getID(), $objB->getID());
    }

    public function testTagQueries() {
        $obj = new Element();
        $obj->addTag("a.b");
        $obj->addTag("a.b.c", 'value');
        $obj->addTag("a.b.c");

        $this->assertEquals(array("a.b", "a.b.c"), $obj->listTags());

        $this->assertEquals(true, $obj->getTag('a.b'));
        $this->assertEquals("value", $obj->getTag('a.b.c'));

        $this->assertThrows('Exception', array($obj, 'addTag'), 'a.b.c', 'value2');
    }
}