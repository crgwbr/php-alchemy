<?php

namespace Alchemy\tests;
use Alchemy\util\promise\Waitable;
use Alchemy\util\promise\TypeException;
use Alchemy\util\promise\TimeoutException;


class WaitableTest extends BaseTest {
    public function testResolution() {
        $waitable = new Waitable();

        $this->assertFalse($waitable->check());
        $this->assertSame(null, $waitable());

        $waitable->resolve(null);
        $this->assertFalse($waitable->check());
        $this->assertSame(null, $waitable());

        // Exceptions and values cause check() to be true
        $waitable->resolve($obj = new \Exception());
        $this->assertTrue($waitable->check());
        $this->assertSame($obj, $waitable());

        $waitable->resolve(5);
        $this->assertTrue($waitable->check());
        $this->assertSame(5, $waitable());

        // NULL causes check() to be false again
        $waitable->resolve(null);
        $this->assertFalse($waitable->check());
        $this->assertSame(null, $waitable());
    }


    public function testTypeExceptions() {
        $waitable = new Waitable("stdClass");

        $this->assertEquals("stdClass", $waitable->type());

        // correct types are passed
        $waitable->resolve($obj = new \stdClass());
        $this->assertTrue($waitable->check());
        $this->assertSame($obj, $waitable());

        // wrong types result in a TypeException
        $waitable->resolve($obj = new \Alchemy\core\query\Scalar(4));
        $this->assertTrue($waitable->check());
        $this->assertInstanceOf("Alchemy\util\promise\TypeException", $waitable());

        // Exceptions & Waitables are passed automatically
        $waitable->resolve($obj = new \Exception());
        $this->assertTrue($waitable->check());
        $this->assertInstanceOf("Exception", $waitable());

        $waitable->resolve($obj = new Waitable());
        $this->assertTrue($waitable->check());
        $this->assertInstanceOf("Alchemy\util\promise\Waitable", $waitable());
    }


    public function testWaitResolve() {
        $waitable = new Waitable();

        // causes wait() to not sleep at all
        $waitable->timeout();

        // wait() returns the Exception, expect() throws it
        $this->assertInstanceOf("Alchemy\util\promise\TimeoutException", $waitable->wait());
        $this->assertThrows("Alchemy\util\promise\TimeoutException",
            array($waitable, 'expect'));

        // as well as any exception it resolves to
        $waitable->resolve($obj = new \Exception());
        $this->assertThrows($obj, array($waitable, 'expect'));

        // otherwise, they return the same
        $waitable->resolve(5);
        $this->assertSame(5, $waitable->wait());
        $this->assertSame(5, $waitable->expect());
    }
}