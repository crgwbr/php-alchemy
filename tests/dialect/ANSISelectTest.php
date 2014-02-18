<?php

namespace Alchemy\tests;
use Alchemy\core\schema\Table;
use Alchemy\core\query\Expression as E;
use Alchemy\core\query\Query;
use Alchemy\dialect\ANSICompiler;


class ANSISelectTest extends BaseTest {

    public function testSimpleSelect() {
        $users = new Table('users', array(
            'UserID' => 'Integer(11)',
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $users = $users->getRef();

        $query = Query::Select($users)
            ->columns($users->UserName, $users->Email)
            ->limit(2);

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query, array('alias_tables' => true));

        $this->assertExpectedString('ANSISelectTest-1.sql', $vern);
    }


    public function testSingleJoinSelect() {
        $users = new Table('users', array(
            'UserID' => 'Integer',
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $addrs = new Table('addresses', array(
            'UserID' => 'Integer',
            'StreetAddress' => 'String'
        ));

        $addrs = $addrs->getRef();
        $users = $users->getRef();

        $query = Query::Select($users)
            ->columns($users->UserName, $users->Email, $addrs->StreetAddress)
            ->join($addrs, $addrs->UserID->equal($users->UserID));

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query, array('alias_tables' => true));

        $this->assertExpectedString('ANSISelectTest-2.sql', $vern);
    }


    public function testMultiJoinSelect() {
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

        $addrs = $addrs->getRef();
        $users = $users->getRef();
        $phones = $phones->getRef();

        $addrJoin = E::AND_($addrs->UserID->equal($users->UserID),
                            $addrs->AddressType->equal(5));

        $phoneJoin = $phones->UserID->equal($users->UserID);

        $query = Query::Select($users)
            ->columns($users->UserName, $addrs->StreetAddress, $phones->PhoneNum)
            ->join($addrs, $addrJoin)
            ->join($phones, $phoneJoin);

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query, array('alias_tables' => true));

        $this->assertExpectedString('ANSISelectTest-3.sql', $vern);
    }


    public function testWhereSelect() {
        $users = new Table('users', array(
            'UserID' => 'Integer',
            'UserName' => 'String',
        ));

        $users = $users->getRef();

        $query = Query::Select($users)
            ->columns($users->UserID, $users->UserName)
            ->where($users->UserName->equal('user1@example.com'))
            ->offset(2)->limit(5);

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query, array('alias_tables' => true));

        $this->assertExpectedString('ANSISelectTest-4.sql', $vern);
    }
}
