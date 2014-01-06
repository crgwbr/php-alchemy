<?php

namespace Alchemy\tests;
use Alchemy\util\promise\Signal;


class SignalTest extends BaseTest {
    public function testSourceForwarding() {
        // every Signal check() will call $this->fnCallback()
        $this->expectsCallback($this->exactly(4))
            ->will($this->onConsecutiveCalls(null, null, 4, 5));
        $signal = new Signal($this->fnCallback);

        $this->assertFalse($signal->check());
        $this->assertSame(null, $signal());
        $this->assertTrue($signal->check());
        $this->assertSame(5,    $signal());


        // passing a Signal to a Signal will redirect its source
        // therefore, this will resolve to NULL, NULL, 5 but never 7
        $this->expectsCallback($this->exactly(2))
            ->will($this->onConsecutiveCalls(null, 5));
        $signal = new Signal($this->fnCallback);

        $this->expectsCallback($this->exactly(2))
            ->will($this->onConsecutiveCalls(null, $signal, 7));
        $signal = new Signal($this->fnCallback);

        $this->assertFalse($signal->check());
        $this->assertSame(null, $signal());
        $this->assertSame(5,    $signal());
    }
}