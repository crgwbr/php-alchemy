<?php

namespace Alchemy\tests;
use Alchemy\expression\Table;
use Alchemy\expression\Insert;
use Alchemy\dialect\DialectTranslator;


class ANSIInsertTest extends BaseTest {

    public function testSimpleInsert() {
        $users = new Table('users', array(
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $query = Insert::init()->columns($users->UserName, $users->Email)
                               ->into($users)
                               ->row("user1", "user1@example.com");

        $translator = new DialectTranslator('ANSI');
        $vern = $translator->translate($query->unwrap());

        $this->assertExpectedString('ANSIInsertTest-1.sql', (string)$vern);
    }
}
