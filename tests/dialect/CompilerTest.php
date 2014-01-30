<?php

namespace Alchemy\tests;

use Alchemy\dialect\Compiler;


class CompilerTest extends BaseTest {

    public function testConfigStack() {
        $comp = new Compiler(array('key' => true));

        $this->assertTrue($comp->getConfig('key'));

        $comp->pushConfig(array('key' => false));
        $this->assertFalse($comp->getConfig('key'));

        $comp->popConfig();
        $this->assertTrue($comp->getConfig('key'));

        $comp->popConfig();
        $this->assertEquals(null, $comp->getConfig('key'));
    }


    public function testFormatRecursion() {
        $sub = array('A', 'B', 'C', 'D');
        $elements = array('X', 'Y', 'Z', $sub, $sub, $sub);

        // applies /// to tail elements of array, and !!! to their sub-elements
        $format = "%s and %s recurse (%4/%s into %1!!!/, /)";
        $result = "X and Y recurse (A into ABCD, A into ABCD, A into ABCD)";

        $comp = new Compiler();
        $this->assertEquals($result, $comp->format($format, $elements));

        $elements = array('A', array('', 'B', ''), array('', ''));
        $format = "%s (%2/%!!+!/, /)";

        $this->assertEquals("A (B)", $comp->format($format, $elements));

        $elements = array('@', $sub, $sub);
        $format = "{%2$3//, /} %1\$s (%3$2// + /)";

        $this->assertEquals("{C, D} @ (B + C + D)", $comp->format($format, $elements));
    }
}