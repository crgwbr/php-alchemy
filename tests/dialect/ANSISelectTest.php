<?php

namespace Alchemy\tests;
use Alchemy\expression\Table;
use Alchemy\expression\Select;
use Alchemy\dialect\ANSICompiler;


class ANSISelectTest extends BaseTest {

    public function testSimpleSelect() {
        $users = new Table('users', array(
            'UserID' => 'Integer(11)',
            'UserName' => 'String',
            'Email' => 'String',
        ));

        $query = Select::init()->columns($users->UserName, $users->Email)
                               ->from($users)
                               ->limit(2);

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query->unwrap(), array('alias_tables' => true));

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

        $query = Select::init()->columns($users->UserName, $users->Email, $addrs->StreetAddress)
                               ->from($users)
                               ->join($addrs, $addrs->UserID->equal($users->UserID));

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query->unwrap(), array('alias_tables' => true));

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

        $addrJoin = $addrs->UserID
                          ->equal($users->UserID)
                          ->and($addrs->AddressType->equal(5));

        $phoneJoin = $phones->UserID->equal($users->UserID);

        $query = Select::init()->columns($users->UserName, $addrs->StreetAddress, $phones->PhoneNum)
                               ->from($users)
                               ->join($addrs, $addrJoin)
                               ->join($phones, $phoneJoin);

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query->unwrap(), array('alias_tables' => true));

        $this->assertExpectedString('ANSISelectTest-3.sql', $vern);
    }


    public function testWhereSelect() {
        $users = new Table('users', array(
            'UserID' => 'Integer',
            'UserName' => 'String',
        ));

        $query = Select::init()->columns($users->UserID, $users->UserName)
                               ->from($users)
                               ->where($users->UserName->equal('user1@example.com'))
                               ->limit(2, 5);

        $ansi = new ANSICompiler();
        $vern = $ansi->compile($query->unwrap(), array('alias_tables' => true));

        $this->assertExpectedString('ANSISelectTest-4.sql', $vern);
    }
}
