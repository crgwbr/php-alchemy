<?php

use Alchemy\expression\Table;
use Alchemy\expression\Column;
use Alchemy\expression\QueryManager;
use Alchemy\expression\String;


class InsertQueryTest extends BaseTest {

    public function testSimpleInsert() {
        $users = new Table('users');
        $userName = new Column($users, 'UserName');
        $email = new Column($users, 'Email');

        $query = new QueryManager();
        $query = $query->insert($userName, $email)
                       ->into($users)
                       ->row(new String("user1"), new String("user1@example.com"));

        $this->assertExpectedString('InsertQueryTest-1.sql', (string)$query);
    }
}
