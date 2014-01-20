<?php

namespace Alchemy\tests;
use Alchemy\util\promise\Promise;
use Alchemy\expression\ForeignKey;
use Alchemy\expression\Integer;
use Alchemy\expression\Table;


class ForeignKeyTest extends BaseTest {

    public function testCopy() {
        $table = new Table('Table', array(
            'Int' => new Integer(array(11, 'null' => true))));

        $fk = new ForeignKey('self.Int');

        $col = $fk->copy(array(), $table, 'FK');

        // inherits type details, parent table
        $this->assertInstanceOf("Alchemy\expression\Integer", $col);
        $this->assertEquals($table, $col->getTable());
        $this->assertEquals(11, $col->getSize());
        $this->assertEquals(53, $col->decode('53'));

        // but not meta details like NOT NULL
        $this->assertFalse($table->Int->isNotNull());
        $this->assertTrue($col->isNotNull());

        // new column should have an FK constraint to the right column
        $this->assertEquals($table->Int, $col->getArg('foreign_key'));

        // created column can be used in another foreign key
        $fk = new ForeignKey(array($col, 'null' => true));
        $colB = $fk->copy();
        $this->assertInstanceOf("Alchemy\expression\Integer", $colB);
        $this->assertEquals($col, $colB->getArg('foreign_key'));
        $this->assertEquals(11, $colB->getSize());
        $this->assertFalse($colB->isNotNull());
    }


    public function testRegisteredTable() {
        $table = new Table('Table', array(
            'Int' => new Integer(11)));

        $fk = new ForeignKey('Table.Int');

        $table->register();

        $col = $fk->copy();
        $this->assertEquals($table->Int, $col->getArg('foreign_key'));
    }


    public function testSelfReferencingTable() {
        $table = new Table('Table', array(
            'Int' => new Integer(11),
            'FK'  => new ForeignKey(array('self.Int', 'null' => true)) ));

        $this->assertInstanceOf("Alchemy\expression\Integer", $table->FK);
        $this->assertEquals($table, $table->FK->getTable());
        $this->assertEquals(11, $table->FK->getSize());

        // invalid because key is not attached to a table
        $col = new ForeignKey('self.Int');
        $this->assertThrows("Exception", array($col, 'copy'));
    }


    public function testSelfReferencingTablePromise() {
        // Table promise with self-referencing foreign key
        $table = new Promise(function() use (&$table) {
            return new Table('Table', array(
                'PK' => new Integer(11),
                'FK' => new ForeignKey($table->PK) ));
        }, "Alchemy\expression\Table");

        $this->assertInstanceOf("Alchemy\util\promise\Promise", $table->FK);
        $this->assertEquals(11, $table->FK->getSize());

        // foreign key promise
        $this->assertEquals('PK', $table->FK->getArg('foreign_key')->getName());
    }
}
