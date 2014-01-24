<?php

namespace Alchemy\tests;
use Alchemy\expression\Table;
use Alchemy\expression\Select;
use Alchemy\expression\Scalar;
use Alchemy\expression\Expression as E;


class SelectTest extends BaseTest {

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

        $addrJoin = E::AND_($addrs->UserID->equal($users->UserID),
                            $addrs->AddressType->equal(5));

        $phoneJoin = $phones->UserID->equal($users->UserID);

        $query = Select::init()->columns($users->UserName, $addrs->StreetAddress, $phones->PhoneNum)
                               ->from($users)
                               ->join($addrs, $addrJoin)
                               ->join($phones, $phoneJoin);

        $params = $query->getParameters();
        $this->assertEquals(1, count($params));
        $this->assertTrue($params[0] instanceof Scalar);
        $this->assertEquals(5, $params[0]->getValue());
    }
}
