<?php

namespace Alchemy\tests;
use Alchemy\expression\Bool;
use Alchemy\expression\Scalar;


class BoolTest extends BaseTest {

    public function testBool() {
        $col = new Bool('t', 'c', 'a', array(), array());

        $this->assertEquals(false, $col->decode('0'));
        $this->assertEquals(true, $col->decode('1'));

        $this->assertEquals(new Scalar(false), $col->encode(false));
        $this->assertEquals(new Scalar(true), $col->encode(1));
    }
}
