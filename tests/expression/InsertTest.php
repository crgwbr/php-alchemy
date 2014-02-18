<?php

namespace Alchemy\tests;
use Alchemy\core\schema\Table;
use Alchemy\core\query\Query;
use Alchemy\core\query\Scalar;


class InsertTest extends BaseTest {

    public function testSimpleInsert() {
        $users = new Table('users', array(
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $users = $users->getRef();

        $query = Query::Insert($users)
            ->columns($users->UserName)
            ->row("user1")
            ->row("user2");

        $query->Email = "user1@example.com";

        $params = array(new Scalar("user1@example.com"), new Scalar("user1"), new Scalar("user2"));
        $this->assertEquals($params, $query->parameters());
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

        $params = array(new Scalar("user1@example.com"));
        $this->assertEquals($params, $query->parameters());
    }
}
