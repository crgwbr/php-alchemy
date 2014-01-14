<?php

namespace Alchemy\tests;
use Alchemy\expression\Table;
use Alchemy\expression\Delete;
use Alchemy\dialect\ANSICompiler;


class ANSIDeleteTest extends BaseTest {

    public function testSimpleDelete() {
        $users = new Table('users', array(
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $query = Delete::init()->from($users)
                               ->where($users->Email->equal("user@example.com"));

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query->unwrap(), array('alias_tables' => true));

        $this->assertExpectedString('ANSIDeleteTest-1.sql', $vern);
    }
}
