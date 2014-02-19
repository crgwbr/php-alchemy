<?php

namespace Alchemy\tests;
use Alchemy\core\schema\Table;
use Alchemy\core\query\Scalar;
use Alchemy\core\query\Query;
use Alchemy\core\query\Expression as E;


class QueryTest extends BaseTest {

    public function testGetParams() {
        $users = Table::Core('users', array(
            'columns' => array(
                'UserID' => 'Integer(11)',
                'UserName' => 'String',
                'Email' => 'String')
        ));

        $addrs = Table::Core('addresses', array(
            'columns' => array(
                'UserID' => 'Integer',
                'AddressType' => 'Integer',
                'StreetAddress' => 'String')
        ));

        $phones = Table::Core('phones', array(
            'columns' => array(
                'UserID' => 'Integer',
                'PhoneNum' => 'String')
        ));

        $addrs  = $addrs->getRef();
        $users  = $users->getRef();
        $phones = $phones->getRef();

        $query = Query::Query($users)
            ->columns(array('Scalar', "value"), $users->UserName)
            ->join($addrs, E::AND_(
                $addrs->UserID->equal($users->UserID),
                $addrs->AddressType->equal(5) ))
            ->join($phones,
                $phones->UserID->equal($users->UserID));

        $query->Aliased  = $addrs->StreetAddress;
        $query->PhoneNum = $phones->PhoneNum;
        $query->Function = E::LOWER($users->UserName);

        $params = array(new Scalar("value"), new Scalar(5));
        $this->assertEquals($params, $query->parameters());

        $columns = array(
            'UserName' => $users->UserName,
            'Aliased'  => $addrs->StreetAddress,
            'PhoneNum' => $phones->PhoneNum,
            'Scalar'   => new Scalar("value"),
            'Function' => E::LOWER($users->UserName));
        $this->assertEquals($columns, $query->columns());
    }
}
