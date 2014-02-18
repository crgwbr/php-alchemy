<?php

namespace Alchemy\tests;
use Alchemy\core\schema\Table;
use Alchemy\core\query\Query;
use Alchemy\dialect\ANSICompiler;


class ANSIDeleteTest extends BaseTest {

    public function testSimpleDelete() {
        $users = new Table('users', array(
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $users = $users->getRef();

        $query = Query::Delete($users)
            ->where($users->Email->equal("user@example.com"));

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query, array('alias_tables' => true));

        $this->assertExpectedString('ANSIDeleteTest-1.sql', $vern);
    }
}
