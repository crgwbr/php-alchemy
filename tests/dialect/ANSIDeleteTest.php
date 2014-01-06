<?php

namespace Alchemy\tests;
use Alchemy\expression\Table;
use Alchemy\expression\Delete;
use Alchemy\dialect\DialectTranslator;


class ANSIDeleteTest extends BaseTest {

    public function testSimpleDelete() {
        $users = new Table('users', array(
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $query = Delete::init()->from($users)
                               ->where($users->Email->equal("user@example.com"));

        $translator = new DialectTranslator('ANSI');
        $vern = $translator->translate($query->unwrap());

        $this->assertExpectedString('ANSIDeleteTest-1.sql', (string)$vern);
    }
}
