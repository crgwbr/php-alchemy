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
        $obj->addTags(array(
            "a.b.c" => true,
            "b.c"   => "value2"));

        $this->assertEquals(array("a.b", "a.b.c", "b.c"), $obj->listTags());

        $this->assertEquals(true, $obj->getTag('a.b'));
        $this->assertEquals("value", $obj->getTag('a.b.c'));
        $this->assertEquals("value2", $obj->getTag('b.c'));

        $this->assertThrows('Exception', array($obj, 'addTag'), 'a.b.c', 'value2');
    }

    public function testDefinitionInheritance() {
        // doesn't know what to inherit from yet
        $this->assertThrows('Exception', array("Element", "define"), "Type", null);
        $this->assertThrows('Exception', array("Element", "define"), "Type", "Unknown");

        // defaults to 'Element' type
        Element::define(null, null, array('key' => 'element'));

        $expected = array('key' => 'element',
            'tags' => array(
                'element.type' => 'Element',
                'element.class' => 'Alchemy\expression\Element'));
        $this->assertEquals($expected, Element::get_definition('Element'));

        // inherits from default 'Element' type
        Element::define('Type', null, array('key' => 'type', 'a' => array(1, 'k' => 'v')));
        Element::define('Subtype', 'Type', array('a' => array(2)));

        $expected = array('key' => 'type',
            'a' => array(2, 'k' => 'v'),
            'tags' => array(
                'element.type' => 'Subtype',
                'element.class' => 'Alchemy\expression\Element'));
        $this->assertEquals($expected, Element::get_definition('Subtype'));

        // doesn't exist
        $this->assertThrows('Exception', array("Element", "get_definition"), "Unknown");

        $obj = Element::Subtype();
        $this->assertInstanceOf('Alchemy\expression\Element', $obj);
        $this->assertEquals('Subtype', $obj->getType());
    }

    public function testDefinitionAliases() {
        // aliases Element::Mock to MockElement::Mock
        Element::define('Type', 'Type', array('key' => 'type'));
        MockElement::define('Mock', 'Element::Type', array('key2' => 'mock'));
        Element::define_alias('Mock', 'MockElement::Mock');

        $expected = array('key' => 'type',
            'key2' => 'mock',
            'tags' => array(
                'element.type' => 'Mock',
                'element.class' => 'Alchemy\tests\MockElement'));
        $this->assertEquals($expected, Element::get_definition('Mock'));

        $obj = Element::Mock();
        $this->assertInstanceOf('Alchemy\tests\MockElement', $obj);
        $this->assertEquals('Mock', $obj->getType());
    }
}


class MockElement extends Element {}
