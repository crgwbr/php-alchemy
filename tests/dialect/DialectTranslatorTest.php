<?php

namespace Alchemy\tests;
use Alchemy\dialect\DialectTranslator;
use Alchemy\expression\Scalar;
use Alchemy\expression\Select;


class DialectTranslatorTest extends BaseTest {

    public function testTranslate() {
        $query = Select::init()->column(new Scalar('hello'));

        $translator = new DialectTranslator('ANSI');
        $vern = $translator->translate($query->unwrap());
        $this->assertEquals('SELECT :p0', (string)$vern);
    }
}
