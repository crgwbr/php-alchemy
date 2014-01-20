<?php

namespace Alchemy\tests;
use Alchemy\util\promise\Promise;
use Alchemy\util\promise\IPromisable;


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


    public function testMagicPassing() {
        $obj = new \stdClass();
        $promise = new Promise(null);
        $promise->resolve($obj);

        // magic-mapped to resolved object
        $obj->key = "value";
        $this->assertEquals("value", $promise->key);
    }


    public function testPromisableCallChain() {
        $cls = 'Alchemy\tests\MockPromisable';
        $this->assertEquals($cls, Promise::get_return_type($cls, "promisableMethod"));
        $this->assertEquals(false, Promise::get_return_type($cls, "normalMethod"));

        // promisable methods can be called on an unresolved typed promise
        $promiseA = new Promise(null, $cls);
        $promiseB = $promiseA
            ->promisableMethod(3, 'q')
            ->promisableMethod(3, 'q');

        // B is a Promise of calling ->promisableMethod(3, 'q') on A's result
        $this->assertInstanceOf("Alchemy\util\promise\Promise", $promiseB);

        $mock = $this->getMock($cls, array('promisableMethod'));
        $mock->expects($this->exactly(2))
            ->method('promisableMethod')
            ->with(3, 'q')
            ->will($this->returnValue($mock));

        $promiseA->resolve($mock);

        // this call will force the Promise to resolve
        $this->assertEquals("value", $promiseB->normalMethod());

        // cache simple promisable methods
        $this->assertEquals($promiseA->promisableMethod(), $promiseA->promisableMethod());
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


class MockPromisable implements IPromisable {

    public static function list_promisable_methods() {
        return array('promisableMethod' => 'Alchemy\tests\MockPromisable');
    }

    public function promisableMethod() {
        return new self();
    }

    public function normalMethod() {
        return "value";
    }
}
