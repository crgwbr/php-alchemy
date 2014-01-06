<?php

namespace Alchemy\tests;
use Alchemy\util\promise\Promise;


class PromiseTest extends BaseTest {
    public function testDoubleResolve() {
        $promise = new Promise(null);
        $this->assertEquals(null, $promise());

        $promise->resolve(4);
        $this->assertEquals(4, $promise());

        // ignores second resolve
        $promise->resolve(5);
        $this->assertEquals(4, $promise());
    }


    public function testSignalChange() {
        $this->expectsCallback($this->exactly(2))
            ->will($this->onConsecutiveCalls(null, 5, 3));

        $promise = new Promise($this->fnCallback);

        // once it resolves, it ignores future changes
        $this->assertEquals(null, $promise());
        $this->assertEquals(5, $promise());
        $this->assertEquals(5, $promise());
    }


    public function testGetSetCallPassing() {
        $promise = new Promise(null);
        $promise->timeout();

        // expects()s it to resolve so it can call_user_func() on result
        $this->assertThrows("Exception", array($promise, 'func'), 4);

        $obj = new \stdClass();
        $promise = new Promise(null);
        $promise->resolve($obj);

        // magic-mapped to resolved object
        $promise->key = "value";
        $this->assertEquals("value", $promise->key);
        $this->assertEquals("value", $obj->key);
    }


    public function testThenComposition() {
        $promise = new Promise(4);

        // called with non-null, non-Exception values
        $this->expectsCallback($this->once())->with(4)
            ->will($this->returnValue(15));
        $promise = $promise->then($this->fnCallback);

        $this->assertEquals(15, $promise());
        $this->assertEquals(15, $promise());
    }


    public function testThenTypeCatching() {
        // catches non-null, non-Exception values
        $this->expectsCallback($this->once())->with(12)
            ->will($this->returnValue(17));
        $fnThen = $this->fnCallback;

        // catches Exception values
        $obj = new \Exception();
        $this->expectsCallback($this->once())->with($obj)
            ->will($this->returnValue(12));
        $fnFail = $this->fnCallback;

        $source  = new Promise(null);
        $promise = $source
            ->then($fnThen)           // ignores Exception
            ->then(null, $fnFail)     // catches Exception, returns 12
            ->then($fnThen, $fnFail); // catches 12, returns 17

        // then()'s won't be called until the Promise resolves
        $this->assertEquals(null, $promise());

        // automatically cascades through then()'s
        $source->resolve($obj);
    }


    public function testThenSourceForwarding() {
        $this->expectsCallback($this->exactly(3))
            ->will($this->onConsecutiveCalls(null, null, 4));
        $then = $this->fnCallback;

        $fnThen = function() use ($then) {
            return new Promise($then);
        };

        $source  = new Promise(null);
        $promise = $source->then($fnThen);

        // cascades to $promise, which forwards to Promise($then)
        $source->resolve(0);

        $this->assertEquals(null, $promise());
        $this->assertEquals(4,    $promise());
    }
}