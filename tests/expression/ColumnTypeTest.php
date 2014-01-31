<?php

namespace Alchemy\tests;
use Alchemy\expression\Column;


class ColumnTypeTest extends BaseTest {

    public function testBool() {
        $col = Column::Bool();

        $this->assertEquals(false, $col->decode('0'));
        $this->assertEquals(true, $col->decode('1'));

        $this->assertEquals(false, $col->encode(false)->getValue());
        $this->assertEquals(true, $col->encode(1)->getValue());
    }

    public function testDecimal() {
        $col = Column::Decimal();

        $this->assertSame('53.5', $col->decode('53.5'));
        $this->assertSame('101', $col->decode('101'));

        $this->assertSame('56', $col->encode('56')->getValue());
        $this->assertSame('200.6789', $col->encode('200.6789')->getValue());
    }

    public function testInteger() {
        $col = Column::Integer();

        $this->assertEquals(53, $col->decode('53'));
        $this->assertEquals(101, $col->decode('101'));

        $this->assertEquals(56, $col->encode(56)->getValue());
        $this->assertEquals(200, $col->encode(200)->getValue());
    }

    public function testString() {
        $col = Column::String();

        $this->assertEquals('42', $col->decode(42));
        $this->assertEquals('hello', $col->decode('hello'));
        $this->assertEquals('hello', $col->encode('hello')->getValue());
    }

    public function testTimestamp() {
        $col = Column::Timestamp();

        $this->assertEquals(new \Datetime('2008-12-01 00:00:00'), $col->decode('2008-12-01 00:00:00'));
        $this->assertEquals('2008-12-01 00:00:00', $col->encode(new \Datetime('2008-12-01 00:00:00'))->getValue());
    }
}
