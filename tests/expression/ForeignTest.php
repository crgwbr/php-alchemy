<?php

namespace Alchemy\tests;
use Alchemy\util\promise\Promise;
use Alchemy\expression\Foreign;
use Alchemy\expression\Integer;
use Alchemy\expression\Table;


class ForeignTest extends BaseTest {

    public function testCopy() {
        $table = new Table('Table', array(
            'Int' => new Integer(array(11, 'null' => true))));

        $fk = new Foreign('self.Int');

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
        $this->assertEquals(array($table->Int), $col->getForeignKey()->listSources());

        // created column can be used in another foreign key
        $fk = new Foreign(array($col, 'null' => true));
        $colB = $fk->copy();
        $this->assertInstanceOf("Alchemy\expression\Integer", $colB);
        $this->assertEquals(array($col), $colB->getForeignKey()->listSources());
        $this->assertEquals(11, $colB->getSize());
        $this->assertFalse($colB->isNotNull());
    }


    public function testRegisteredTable() {
        $table = new Table('Table', array(
            'Int' => new Integer(11)));

        $fk = new Foreign('Table.Int');

        $table->register();

        $col = $fk->copy();
        $this->assertEquals(array($table->Int), $col->getForeignKey()->listSources());
    }


    public function testSelfReferencingTable() {
        $table = new Table('Table', array(
            'Int' => new Integer(11),
            'FK'  => new Foreign(array('self.Int', 'null' => true)) ));

        $this->assertInstanceOf("Alchemy\expression\Integer", $table->FK);
        $this->assertEquals($table, $table->FK->getTable());
        $this->assertEquals(11, $table->FK->getSize());

        // invalid because key is not attached to a table
        $col = new Foreign('self.Int');
        $this->assertThrows("Exception", array($col, 'copy'));
    }


    public function testSelfReferencingTablePromise() {
        // Table promise with self-referencing foreign key
        $table = new Promise(function() use (&$table) {
            return new Table('Table', array(
                'PK' => new Integer(11),
                'FK' => new Foreign($table->PK) ));
        }, "Alchemy\expression\Table");

        $this->assertInstanceOf("Alchemy\util\promise\Promise", $table->FK);
        $this->assertEquals(11, $table->FK->getSize());

        // foreign key promise
        $sources = $table->FK->getForeignKey()->listSources();
        $this->assertInstanceOf("Alchemy\util\promise\Promise", $sources[0]);
    }
}
