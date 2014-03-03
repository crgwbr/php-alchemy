<?php

namespace Alchemy\tests;
use Alchemy\core\query\Expression;
use Alchemy\core\query\Scalar;
use Alchemy\core\query\Predicate;


class ExpressionTest extends BaseTest {

    public function testGetParameters() {
        Expression::define('many', null, array('arity' => -1));

        $expr = Expression::many(
            new Scalar(5),
            Expression::many(
                new Scalar(9),
                new Scalar(3)) );

        $scalars = $expr->parameters();
        $this->assertEquals(3, count($scalars));
        $this->assertEquals(5, $scalars[0]->getValue());
        $this->assertEquals(3, $scalars[2]->getValue());
    }


    public function testStaticOperators() {
        $this->assertInstanceOf('Alchemy\core\query\Predicate', Expression::AND_());
    }


    public function testRoleChecks() {
        $int  = new Scalar(3);
        $prd  = Predicate::isNull($int);

        Expression::define('zero', null, array('arity' => 0));
        Expression::define('one',  null, array('arity' => 1));
        Expression::define('many', null, array('arity' => -1));

        $expr = Expression::zero();
        $expr = Expression::one ($int);
        $expr = Expression::many();
        $expr = Expression::many($int, $int, $int);
        $this->assertEquals(true, $expr->getTag('expr.value'));

        // tag check fail
        $this->assertThrows('Exception', function() use ($prd) {
            $expr = Expression::many($prd);
        });

        // arity fail
        $this->assertThrows('Exception', function() use ($int) {
            $expr = Expression::one();
        });

        $this->assertThrows('Exception', function() use ($int) {
            $expr = Expression::zero($int);
        });
    }


    public function testPredicateAll() {
        $lt = Predicate::lt(new Scalar(2), new Scalar(5));
        $eq = Predicate::equal(new Scalar(4), new Scalar(4));
        $and = Predicate::AND_($lt, $eq);
        $or = Predicate::OR_($eq, $lt);

        $all = Predicate::AND_($lt, $eq, $or, $eq);
        $this->assertEquals($all, Predicate::all(array($lt, $eq, $or, $eq)));
        $this->assertEquals($all, Predicate::all(array(array($lt, array($eq, $or)), array($eq))));
        $this->assertEquals($all, Predicate::all(array($and), $or, array($eq)));
        $this->assertEquals($all, Predicate::all(Predicate::AND_($and, $or, $eq)));

        $this->assertEquals($lt, Predicate::all($lt));
        $this->assertEquals($and, Predicate::all($and));
    }
}
