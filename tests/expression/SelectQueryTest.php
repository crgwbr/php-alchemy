<?php

namespace Alchemy\tests;
use Alchemy\expression\Table;
use Alchemy\expression\Column;
use Alchemy\expression\QueryManager;
use Alchemy\expression\BinaryExpression;
use Alchemy\expression\CompoundExpression;
use Alchemy\expression\Operator;
use Alchemy\expression\Integer;
use Alchemy\expression\Scalar;


class SelectQueryTest extends BaseTest {

    public function testGetParams() {
        $users = new Table('users', array(
            'UserID' => 'Integer(11)',
            'UserName' => 'String',
        ));

        $addrs = new Table('addresses', array(
            'UserID' => 'Integer',
            'AddressType' => 'Integer',
            'StreetAddress' => 'String'
        ));

        $phones = new Table('phones', array(
            'UserID' => 'Integer',
            'PhoneNum' => 'String'
        ));

        $addrJoin = $addrs->UserID
                          ->equal($users->UserID)
                          ->and($addrs->AddressType->equal(5));

        $phoneJoin = $phones->UserID->equal($users->UserID);

        $query = new QueryManager();
        $query = $query->select($users->UserName, $addrs->StreetAddress, $phones->PhoneNum)
                       ->from($users)
                       ->join($addrs, $addrJoin)
                       ->join($phones, $phoneJoin);

        $params = $query->getParameters();
        $this->assertEquals(1, count($params));
        $this->assertTrue($params[0] instanceof Scalar);
        $this->assertEquals(5, $params[0]->getValue());
    }
}
