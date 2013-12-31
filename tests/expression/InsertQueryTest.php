<?php

namespace Alchemy\tests;
use Alchemy\expression\Table;
use Alchemy\expression\Column;
use Alchemy\expression\QueryManager;
use Alchemy\expression\Scalar;


class InsertQueryTest extends BaseTest {

    public function testSimpleInsert() {
        $users = new Table('users', array(
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $query = new QueryManager();
        $query = $query->insert($users->UserName, $users->Email)
                       ->into($users)
                       ->row(new Scalar("user1"), new Scalar("user1@example.com"));

        $params = $query->getParameters();
        $this->assertEquals(2, count($params));
        $this->assertTrue($params[0] instanceof Scalar);
        $this->assertEquals("user1", $params[0]->getValue());
        $this->assertTrue($params[1] instanceof Scalar);
        $this->assertEquals("user1@example.com", $params[1]->getValue());
    }
}
