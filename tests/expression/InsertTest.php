<?php

namespace Alchemy\tests;
use Alchemy\expression\Table;
use Alchemy\expression\Column;
use Alchemy\expression\Insert;
use Alchemy\expression\Scalar;


class InsertTest extends BaseTest {

    public function testSimpleInsert() {
        $users = new Table('users', array(
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $query = Insert::init()->columns($users->UserName, $users->Email)
                               ->into($users)
                               ->row("user1", "user1@example.com");

        $params = $query->getParameters();
        $this->assertEquals(2, count($params));
        $this->assertTrue($params[0] instanceof Scalar);
        $this->assertEquals("user1", $params[0]->getValue());
        $this->assertTrue($params[1] instanceof Scalar);
        $this->assertEquals("user1@example.com", $params[1]->getValue());
    }
}