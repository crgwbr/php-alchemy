<?php

namespace Alchemy\tests;
use Alchemy\util\promise\Promise;
use Alchemy\expression\Foreign;
use Alchemy\expression\Integer;
use Alchemy\expression\Column;
use Alchemy\expression\Table;


class ForeignTest extends BaseTest {

    public function testCopy() {
        $table = new Table('Table', array(
            'Int' => Column::Integer(array(11, 'null' => true))));

        $fk = Column::Foreign('self.Int');

        $col = $fk->copy(array(), $table, 'FK');

        // inherits type details, parent table
        $this->assertEquals("Integer", $col->getType());
        $this->assertEquals($table, $col->getTable());
        $this->assertEquals(11, $col->getArg(0));
        $this->assertEquals(53, $col->decode('53'));

        // but not meta details like NOT NULL
        $this->assertFalse($table->Int->isNotNull());
        $this->assertTrue($col->isNotNull());

        // new column should have an FK constraint to the right column
        $this->assertEquals(array($table->Int), $col->getForeignKey()->listSources());

        // created column can be used in another foreign key
        $fk = Column::Foreign(array($col, 'null' => true));
        $colB = $fk->copy();
        $this->assertEquals("Integer", $colB->getType());
        $this->assertEquals(array($col), $colB->getForeignKey()->listSources());
        $this->assertEquals(11, $colB->getArg(0));
        $this->assertFalse($colB->isNotNull());
    }


    public function testRegisteredTable() {
        $table = new Table('Table', array(
            'Int' => Column::Integer(11)));

        $fk = Column::Foreign('Table.Int');

        $table->register();

        $col = $fk->copy();
        $this->assertEquals(array($table->Int), $col->getForeignKey()->listSources());
    }


    public function testSelfReferencingTable() {
        $table = new Table('Table', array(
            'Int' => Column::Integer(11),
            'FK'  => Column::Foreign(array('self.Int', 'null' => true)) ));

        $this->assertEquals("Integer", $table->FK->getType());
        $this->assertEquals($table, $table->FK->getTable());
        $this->assertEquals(11, $table->FK->getArg(0));

        // invalid because key is not attached to a table
        $col = Column::Foreign('self.Int');
        $this->assertThrows("Exception", array($col, 'copy'));
    }


    public function testSelfReferencingTablePromise() {
        // Table promise with self-referencing foreign key
        $table = new Promise(function() use (&$table) {
            return new Table('Table', array(
                'PK' => Column::Integer(11),
                'FK' => Column::Foreign($table->PK) ));
        }, "Alchemy\expression\Table");

        $this->assertInstanceOf("Alchemy\util\promise\Promise", $table->FK);
        $this->assertEquals(11, $table->FK->getArg(0));

        // foreign key promise
        $sources = $table->FK->getForeignKey()->listSources();
        $this->assertInstanceOf("Alchemy\util\promise\Promise", $sources[0]);
    }
}
