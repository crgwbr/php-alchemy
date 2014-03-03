<?php

namespace Alchemy\tests;
use Alchemy\core\query\Expression;
use Alchemy\core\query\Join;
use Alchemy\core\query\Query;
use Alchemy\core\schema\Table;


class ORMQueryTest extends BaseTest {

    protected $users;
    protected $addrs;
    protected $phones;

    public function setUp() {
        $this->users = Table::ORM('users', array(
            'columns' => array(
                'UserID' => 'Integer(11, primary_key = true)',
                'UserName' => 'String',
                'Email' => 'String',
            ) ));

        $this->addrs = Table::ORM('addresses', array(
            'columns' => array(
                'UserID' => 'Foreign(users.UserID)',
                'AddressType' => 'Integer',
                'StreetAddress' => 'String',
            ),
            'relationships' => array(
                'User' => 'OneToOne(users, backref = "Address")',
            ) ));

        $this->phones = Table::ORM('phones', array(
            'columns' => array(
                'UserID' => 'Foreign(users.UserID)',
                'PhoneNum' => 'String',
            ),
            'relationships' => array(
                'User' => 'ManyToOne(users, backref = "Phones")',
            ) ));

        $this->users->register(true);
        $this->addrs->getRelationship('User');
        $this->phones->getRelationship('User');
    }

    public function testTableRelated() {
        $this->assertFalse($this->users->hasRelationship('Nil'));
        $this->assertTrue($this->users->hasRelationship('Address'));
        $this->assertTrue($this->users->hasRelationship('Phones'));

        $this->assertEquals('OneToOne',  $this->users->getRelationship('Address')->getType());
        $this->assertEquals('OneToMany', $this->users->getRelationship('Phones')->getType());

        $relationships = array(
            'Address' => $this->users->getRelationship('Address'),
            'Phones' => $this->users->getRelationship('Phones'));
        $this->assertEquals($relationships, $this->users->listRelationships());

        $relationships = array(
            'User' => $this->addrs->getRelationship('User'));
        $this->assertEquals($relationships, $this->addrs->listRelationships());

        $relationships = array(
            'User' => $this->phones->getRelationship('User'));
        $this->assertEquals($relationships, $this->phones->listRelationships());
    }

    public function testRefRelated() {
        $users = $this->users->getRef();
        $addrs = $this->addrs->getRef();
        $phones = $this->phones->getRef();

        $this->assertInstanceOf('Alchemy\orm\ORMTableRef', $users->Address);
        $this->assertInstanceOf('Alchemy\orm\ORMTableRef', $users->Phones);

        $this->assertEquals($this->users, $users->schema());
        $this->assertEquals($this->addrs, $users->Address->schema());
        $this->assertEquals($this->users, $users->Address->User->schema());
        $this->assertEquals($this->phones, $users->Phones->schema());

        $this->assertEquals($users->Address, $users->Address);
        $this->assertEquals(array($users->Address, $users->Phones), $users->relationships());

        $this->assertEquals(null, $users->predicate());

        $expr = $addrs->User->UserID->equal($addrs->UserID);
        $this->assertEquals($expr, $addrs->User->predicate());

        $expr = $users->Address->UserID->equal($users->UserID);
        $this->assertEquals($expr, $users->Address->predicate());

        $expr = $users->Address->User->UserID->equal($users->Address->UserID);
        $this->assertEquals($expr, $users->Address->User->predicate());
    }


    public function testSimpleSelect() {
        $users = $this->users->getRef();

        $query = Query::ORM($users);

        $this->assertEquals($users, $query->table());
        $this->assertEquals($users->columns(), $query->columns());
        $this->assertEquals(array(), $query->joins());
        $this->assertEquals(null, $query->where());

        $expr = Expression::UPPER($users->Address->StreetAddress);
        $query->STREET = $expr;

        // already joined, so this should be ignored
        $query->joins(array($users, $users->Address));

        $join = new Join(Join::LEFT, Join::INNER, $users->Address, $users->Address->predicate());

        $this->assertEquals($users, $query->table());
        $this->assertEquals($users->columns() + array('STREET' => $expr), $query->columns());
        $this->assertEquals(array($join), $query->joins());
        $this->assertEquals(null, $query->where());
    }


    public function testRelatedSelect() {
        $users = $this->users->getRef();

        $query = Query::ORM($users->Phones)
            ->where($users->UserName->like('ex%'));

        $join = new Join(Join::LEFT, Join::INNER, $users, null);
        $expr = Expression::AND_(
            $users->Phones->predicate(),
            $users->UserName->like('ex%'));

        $this->assertEquals($users->Phones, $query->table());
        $this->assertEquals($users->Phones->columns(), $query->columns());
        $this->assertEquals(array($join), $query->joins());
        $this->assertEquals($expr, $query->where());

        // shorthand
        $this->assertEquals($query, $users->Phones->where($users->UserName->like('ex%')));
    }
}
