<?php

namespace Alchemy\tests;
use Alchemy\core\schema\Table;
use Alchemy\core\query\Query;
use Alchemy\dialect\ANSICompiler;


class ANSIInsertTest extends BaseTest {

    public function testSimpleInsert() {
        $users = new Table('users', array(
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $users = $users->getRef();

        $query = Query::Insert($users)
            ->columns($users->UserName, array('Email', "user1@example.com"))
            ->row("user1")
            ->row("user2");

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query);

        $this->assertExpectedString('ANSIInsertTest-1.sql', $vern);
    }

    public function testInsertSelect() {
        $users = new Table('users', array(
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $users = $users->getRef();

        $query = Query::Insert($users)
            ->columns($users->UserName);
        $query->Email = "user1@example.com";

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query);

        $this->assertExpectedString('ANSIInsertTest-2.sql', $vern);
    }
}
