<?php

namespace Alchemy\tests;
use Alchemy\expression\Scalar;



class ScalarTest extends BaseTest {

    public function testScalar() {
        $v = new Scalar(false);
        $this->assertEquals('boolean', $v->getTag('expr.value'));
        $this->assertEquals(false, $v->getValue());

        $v = new Scalar(true);
        $this->assertEquals('boolean', $v->getTag('expr.value'));
        $this->assertEquals(true, $v->getValue());

        $v = new Scalar(null);
        $this->assertEquals('null', $v->getTag('expr.value'));
        $this->assertEquals(null, $v->getValue());

        $v = new Scalar(42);
        $this->assertEquals('integer', $v->getTag('expr.value'));
        $this->assertEquals(42, $v->getValue());

        $v = new Scalar('dolphins');
        $this->assertEquals('string', $v->getTag('expr.value'));
        $this->assertEquals('dolphins', $v->getValue());
    }
}
