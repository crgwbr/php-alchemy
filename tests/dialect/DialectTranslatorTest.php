<?php

namespace Alchemy\tests;
use Alchemy\dialect\DialectTranslator;
use Alchemy\expression\Scalar;
use Alchemy\expression\Operator;
use Alchemy\expression\BinaryExpression;


class DialectTranslatorTest extends BaseTest {

    public function testTranslate() {
        $left = new Scalar('hello');
        $right = new Scalar('world');
        $expr = new BinaryExpression($left, Operator::equal(), $right);

        $translator = new DialectTranslator('ANSI');
        $vern = $translator->translate($expr);
        $this->assertEquals('? = ?', (string)$vern);
    }
}
