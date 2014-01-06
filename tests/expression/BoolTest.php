<?php

namespace Alchemy\tests;
use Alchemy\expression\Bool;
use Alchemy\expression\Scalar;


class BoolTest extends BaseTest {

    public function testBool() {
        $col = new Bool('t', 'c', 'a', array(), array());

        $this->assertEquals(false, $col->decode('0'));
        $this->assertEquals(true, $col->decode('1'));

        $this->assertEquals(false, $col->encode(false)->getValue());
        $this->assertEquals(true, $col->encode(1)->getValue());
    }
}
