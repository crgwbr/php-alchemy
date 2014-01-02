<?php

namespace Alchemy\tests;
use Alchemy\expression\String;
use Alchemy\expression\Scalar;



class StringTest extends BaseTest {

    public function testString() {
        $col = new String('t', 'c', 'a', array(), array());

        $this->assertEquals('42', $col->decode(42));
        $this->assertEquals('hello', $col->decode('hello'));
        $this->assertEquals(new Scalar('hello'), $col->encode('hello'));
    }
}
