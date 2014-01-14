<?php

namespace Alchemy\tests;
use Alchemy\expression\Table;
use Alchemy\expression\Update;
use Alchemy\dialect\ANSICompiler;


class ANSIUpdateTest extends BaseTest {

    public function testSimpleUpdate() {
        $users = new Table('users', array(
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $query = Update::init()->table($users)
                               ->set($users->UserName, "user1")
                               ->set($users->Email, "user1@example.com")
                               ->where($users->Email->equal("user2@example.com"));

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query->unwrap(), array('alias_tables' => true));

        $this->assertExpectedString('ANSIUpdateTest-1.sql', $vern);
    }
}
