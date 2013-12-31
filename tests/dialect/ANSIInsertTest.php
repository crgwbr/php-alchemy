<?php

namespace Alchemy\tests;
use Alchemy\expression\Table;
use Alchemy\expression\Column;
use Alchemy\expression\QueryManager;
use Alchemy\expression\Scalar;
use Alchemy\dialect\DialectTranslator;


class ANSIInsertTest extends BaseTest {

    public function testSimpleInsert() {
        $users = new Table('users', array(
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $query = new QueryManager();
        $query = $query->insert($users->UserName, $users->Email)
                       ->into($users)
                       ->row(new Scalar("user1"), new Scalar("user1@example.com"));

        $translator = new DialectTranslator('ANSI');
        $vern = $translator->translate($query);

        $this->assertExpectedString('ANSIInsertTest-1.sql', (string)$vern);
    }
}
