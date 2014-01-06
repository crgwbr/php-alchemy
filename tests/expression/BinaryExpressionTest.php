<?php

namespace Alchemy\tests;
use Alchemy\expression\BinaryExpression;
use Alchemy\expression\Scalar;
use Alchemy\expression\Operator;


class BinaryExpressionTest extends BaseTest {

    public function testBinaryExpression() {
        $expr = new BinaryExpression(
            new Scalar(5),
            Operator::lt(),
            new Scalar(3));

        $scalars = $expr->getParameters();
        $this->assertEquals(2, count($scalars));
        $this->assertEquals(5, $scalars[0]->getValue());
        $this->assertEquals(3, $scalars[1]->getValue());
    }
}
