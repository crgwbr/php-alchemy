<?php

use Alchemy\expression\Table;
use Alchemy\expression\Column;
use Alchemy\expression\QueryManager;
use Alchemy\expression\BinaryExpression;
use Alchemy\expression\CompoundExpression;
use Alchemy\expression\Operator;
use Alchemy\expression\String;
use Alchemy\expression\Integer;


class SelectQueryTest extends BaseTest {

    public function testSimpleSelect() {
        $users = new Table('users');
        $userID = new Column($users, 'UserID');
        $userName = new Column($users, 'UserName');
        $email = new Column($users, 'Email');

        $query = new QueryManager();
        $query = $query->select($userName, $email)
                       ->from($users);

        $this->assertExpectedString('SelectQueryTest-1.sql', (string)$query);
    }


    public function testSingleJoinSelect() {
        $users = new Table('users');
        $userID = new Column($users, 'UserID');
        $userName = new Column($users, 'UserName');
        $email = new Column($users, 'Email');

        $addrs = new Table('addresses');
        $street = new Column($addrs, "StreetAddress");
        $addrJoin = new BinaryExpression(
            new Column($addrs, 'UserID'),
            Operator::equal(),
            $userID);

        $query = new QueryManager();
        $query = $query->select($userName, $email, $street)
                       ->from($users)
                       ->join($addrs, $addrJoin);

        $this->assertExpectedString('SelectQueryTest-2.sql', (string)$query);
    }


    public function testMultiJoinSelect() {
        $users = new Table('users');
        $userID = new Column($users, 'UserID');
        $userName = new Column($users, 'UserName');

        $addrs = new Table('addresses');
        $street = new Column($addrs, "StreetAddress");

        $addrJoin = new CompoundExpression(new BinaryExpression(
            new Column($addrs, 'UserID'),
            Operator::equal(),
            $userID));
        $addrJoin->and(new BinaryExpression(
            new Column($addrs, "AddressType"),
            Operator::equal(),
            new Integer(5)));

        $phones = new Table('phones');
        $phoneNum = new Column($addrs, "PhoneNum");
        $phoneJoin = new BinaryExpression(
            new Column($phones, 'UserID'),
            Operator::equal(),
            $userID);

        $query = new QueryManager();
        $query = $query->select($userName, $street, $phoneNum)
                       ->from($users)
                       ->join($addrs, $addrJoin)
                       ->join($phones, $phoneJoin);

        $this->assertExpectedString('SelectQueryTest-3.sql', (string)$query);

        $params = $query->getParameters();
        $this->assertEquals(1, count($params));
        $this->assertTrue($params[0] instanceof Integer);
        $this->assertEquals(5, $params[0]->getValue());
    }


    public function testWhereSelect() {
        $users = new Table('users');
        $userID = new Column($users, 'UserID');
        $userName = new Column($users, 'UserName');

        $filter = new BinaryExpression(
            $userName,
            Operator::equal(),
            new String("user1@example.com"));

        $query = new QueryManager();
        $query = $query->select($userID, $userName)
                       ->from($users)
                       ->where($filter);

        $this->assertExpectedString('SelectQueryTest-4.sql', (string)$query);

        $params = $query->getParameters();
        $this->assertEquals(1, count($params));
        $this->assertTrue($params[0] instanceof String);
        $this->assertEquals("user1@example.com", $params[0]->getValue());
    }
}
