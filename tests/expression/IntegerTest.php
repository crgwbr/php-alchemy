<?php

namespace Alchemy\tests;
use Alchemy\expression\Integer;
use Alchemy\expression\Scalar;


class IntegerTest extends BaseTest {

    public function testInteger() {
        $col = new Integer();

        $this->assertEquals(53, $col->decode('53'));
        $this->assertEquals(101, $col->decode('101'));

        $this->assertEquals(56, $col->encode(56)->getValue());
        $this->assertEquals(200, $col->encode(200)->getValue());
    }
}
