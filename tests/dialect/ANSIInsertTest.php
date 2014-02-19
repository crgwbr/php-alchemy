<?php

namespace Alchemy\tests;
use Alchemy\core\schema\Table;
use Alchemy\core\query\Query;
use Alchemy\dialect\ANSICompiler;


class ANSIInsertTest extends BaseTest {

    protected $users;

    public function setUp() {
        $this->users = Table::Core('users', array(
            'columns' => array(
                'UserID' => 'Integer(11)',
                'UserName' => 'String',
                'Email' => 'String')
        ));
    }

    public function testSimpleInsert() {
        $users = $this->users->getRef();

        $query = Query::Insert($users)
            ->columns($users->UserName, array('Email', "user1@example.com"))
            ->row("user1")
            ->row("user2");

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query);

        $this->assertExpectedString('ANSIInsertTest-1.sql', $vern);
    }

    public function testInsertSelect() {
        $users = $this->users->getRef();

        $query = Query::Insert($users)
            ->columns($users->UserName);
        $query->Email = "user1@example.com";

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query);

        $this->assertExpectedString('ANSIInsertTest-2.sql', $vern);
    }
}
