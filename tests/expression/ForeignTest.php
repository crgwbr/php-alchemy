<?php

namespace Alchemy\tests;
use Alchemy\util\promise\Promise;
use Alchemy\core\schema\Foreign;
use Alchemy\core\schema\Integer;
use Alchemy\core\schema\Column;
use Alchemy\core\schema\Table;


class ForeignTest extends BaseTest {

    public function testCopy() {
        $table = Table::Core('Table', array('columns' => array(
            'Int' => Column::Integer(array(11, 'null' => true)) )));

        $fk = Column::Foreign('self.Int');

        $col = $fk->copy(array(), $table, 'FK');

        // inherits type details, parent table
        $this->assertEquals("Integer", $col->getType());
        $this->assertEquals($table, $col->getTable());
        $this->assertEquals(11, $col->getArg(0));
        $this->assertEquals(53, $col->decode('53'));

        // but not meta details like NOT NULL
        $this->assertTrue($table->getColumn('Int')->isNullable());
        $this->assertFalse($col->isNullable());

        // new column should have an FK constraint to the right column
        $this->assertEquals(array($table->getColumn('Int')), $col->getForeignKey()->listSources());

        // created column can be used in another foreign key
        $fk = Column::Foreign(array($col, 'null' => true));
        $colB = $fk->copy();
        $this->assertEquals("Integer", $colB->getType());
        $this->assertEquals(array($col), $colB->getForeignKey()->listSources());
        $this->assertEquals(11, $colB->getArg(0));
        $this->assertTrue($colB->isNullable());
    }


    public function testRegisteredTable() {
        $table = Table::Core('Table', array('columns' => array(
            'Int' => Column::Integer(11) )));

        $fk = Column::Foreign('Table.Int');

        $table->register();

        $col = $fk->copy();
        $this->assertEquals(array($table->getColumn('Int')), $col->getForeignKey()->listSources());
    }


    public function testSelfReferencingTable() {
        $table = Table::Core('Table', array('columns' => array(
            'Int' => Column::Integer(11),
            'FK'  => Column::Foreign(array('self.Int', 'null' => true)) )));

        $col = $table->getColumn('FK');
        $this->assertEquals("Integer", $col->getType());
        $this->assertEquals($table, $col->getTable());
        $this->assertEquals(11, $col->getArg(0));

        // invalid because key is not attached to a table
        $col = Column::Foreign('self.Int');
        $this->assertThrows("Exception", array($col, 'copy'));
    }


    public function testSelfReferencingTablePromise() {
        // Table promise with self-referencing foreign key
        $table = new Promise(function() use (&$table) {
            return Table::Core('Table', array('columns' => array(
                'PK' => Column::Integer(11),
                'FK' => Column::Foreign($table->getColumn('PK')) )));
        }, "Alchemy\core\schema\Table");

        $this->assertInstanceOf("Alchemy\util\promise\Promise", $table->getColumn('FK'));
        $this->assertEquals(11, $table->getColumn('FK')->getArg(0));

        // foreign key promise
        $sources = $table->getColumn('FK')->getForeignKey()->listSources();
        $this->assertInstanceOf("Alchemy\util\promise\Promise", $sources[0]);
    }
}
