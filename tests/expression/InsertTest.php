<?php

namespace Alchemy\tests;
use Alchemy\core\schema\Table;
use Alchemy\core\query\Query;
use Alchemy\core\query\Scalar;


class InsertTest extends BaseTest {

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
            ->columns($users->UserName)
            ->row("user1")
            ->row("user2");

        $query->Email = "user1@example.com";

        $params = array(new Scalar("user1@example.com"), new Scalar("user1"), new Scalar("user2"));
        $this->assertEquals($params, $query->parameters());
    }

    public function testInsertSelect() {
        $users = $this->users->getRef();

        $query = Query::Insert($users)
            ->columns($users->UserName);
        $query->Email = "user1@example.com";

        $params = array(new Scalar("user1@example.com"));
        $this->assertEquals($params, $query->parameters());
    }
}
