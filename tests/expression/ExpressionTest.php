<?php

namespace Alchemy\tests;
use Alchemy\expression\Expression;
use Alchemy\expression\Scalar;
use Alchemy\expression\Predicate;


class ExpressionTest extends BaseTest {

    public function testGetParameters() {
        $expr = new MockExpression('many', array(
            new Scalar(5),
            new MockExpression('many', array(
                new Scalar(9),
                new Scalar(3))) ));

        $scalars = $expr->getParameters();
        $this->assertEquals(3, count($scalars));
        $this->assertEquals(5, $scalars[0]->getValue());
        $this->assertEquals(3, $scalars[2]->getValue());
    }


    public function testStaticOperators() {
        $this->assertInstanceOf('Alchemy\expression\Predicate', Expression::AND_());
    }


    public function testRoleChecks() {
        $int  = new Scalar(3);
        $prd  = new Predicate("isNull", array($int));

        $expr = new MockExpression('zero', array());
        $expr = new MockExpression('one', array($int));
        $expr = new MockExpression('many', array());
        $expr = new MockExpression('many', array($int, $int, $int));
        $this->assertEquals(true, $expr->getTag('expr.value'));

        // tag check fail
        $this->assertThrows('Exception', function() use ($prd) {
            $expr = new MockExpression('many', array($prd));
        });

        // arity fail
        $this->assertThrows('Exception', function() use ($int) {
            $expr = new MockExpression('one', array());
        });

        $this->assertThrows('Exception', function() use ($int) {
            $expr = new MockExpression('zero', array($int));
        });
    }
}


class MockExpression extends Expression {
    protected static $element_tag = 'expr.value';
    protected static $result_tag = 'expr.value';
    protected static $types = array(
        'zero'   => 0,
        'one'    => 1,
        'many'   => -1);
}
