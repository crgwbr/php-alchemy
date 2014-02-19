<?php

namespace Alchemy\tests;
use Alchemy\core\schema\Table;
use Alchemy\core\query\Query;
use Alchemy\dialect\ANSICompiler;


class ANSIUpdateTest extends BaseTest {

    public function testSimpleUpdate() {
        $users = Table::Core('users', array(
            'columns' => array(
                'UserName' => 'String',
                'Email' => 'String')
        ));

        $users = $users->getRef();

        $query = Query::Update($users)
            ->columns(array(
                'UserName' => "user1",
                'Email'    => "user1@example.com"))
            ->where($users->Email->equal("user2@example.com"));

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query, array('alias_tables' => true));

        $this->assertExpectedString('ANSIUpdateTest-1.sql', $vern);
    }
}
