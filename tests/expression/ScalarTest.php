<?php

namespace Alchemy\tests;
use Alchemy\expression\Scalar;



class ScalarTest extends BaseTest {

    public function testScalar() {
        $v = new Scalar(false);
        $this->assertEquals(Scalar::T_BOOL, $v->getDataType());
        $this->assertEquals(false, $v->getValue());

        $v = new Scalar(true);
        $this->assertEquals(Scalar::T_BOOL, $v->getDataType());
        $this->assertEquals(true, $v->getValue());

        $v = new Scalar(null);
        $this->assertEquals(Scalar::T_NULL, $v->getDataType());
        $this->assertEquals(null, $v->getValue());

        $v = new Scalar(42);
        $this->assertEquals(Scalar::T_INT, $v->getDataType());
        $this->assertEquals(42, $v->getValue());

        $v = new Scalar('dolphins');
        $this->assertEquals(Scalar::T_STR, $v->getDataType());
        $this->assertEquals('dolphins', $v->getValue());
    }
}
