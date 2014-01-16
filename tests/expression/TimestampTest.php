<?php

namespace Alchemy\tests;
use Alchemy\expression\Timestamp;
use Alchemy\expression\Scalar;
use Datetime;



class TimestampTest extends BaseTest {

    public function testTimestamp() {
        $col = new Timestamp();

        $this->assertEquals(new Datetime('2008-12-01 00:00:00'), $col->decode('2008-12-01 00:00:00'));
        $this->assertEquals('2008-12-01 00:00:00', $col->encode(new Datetime('2008-12-01 00:00:00'))->getValue());
    }
}