<?php

namespace Alchemy\tests;
use Alchemy\expression\Decimal;
use Alchemy\expression\Scalar;


class DecimalTest extends BaseTest {

    public function testDecimal() {
        $col = new Decimal();

        $this->assertSame('53.5', $col->decode('53.5'));
        $this->assertSame('101', $col->decode('101'));

        $this->assertSame('56', $col->encode('56')->getValue());
        $this->assertSame('200.6789', $col->encode('200.6789')->getValue());
    }
}
