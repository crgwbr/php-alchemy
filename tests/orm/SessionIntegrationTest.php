<?php

namespace Alchemy\tests;
use Alchemy\orm\Session;
use Datetime;


class SessionIntegrationTest extends BaseTest {

    public function testModelRoundTrip() {
        $engines = array(
            $this->getSQLiteEngine(),
            $this->getMySQLEngine(),
        );

        foreach ($engines as $engine) {
            $session = new Session($engine);
            $session->ddl()->dropAll();
            $session->ddl()->createAll();

            // Insert
            $lang = new Language();
            $lang->ISO2Code = 'es';
            $lang->LatestChangeStamp = new DateTime("1984-01-01");
            $session->add($lang);
            $session->commit();

            // Select
            $objects = $session->objects('Alchemy\tests\Language');
            $this->assertEquals(1, count($all = $objects->all()));
            $this->assertEquals(1, count($one = $objects->one()));
            $lang = $all[0];
            $this->assertEquals(1, $lang->LanguageID);
            $this->assertEquals('es', $lang->ISO2Code);
            $this->assertEquals('1984-01-01', $lang->LatestChangeStamp->format('Y-m-d'));

            // Update
            $lang->LatestChangeStamp = new DateTime("1985-06-15");
            $lang->save();
            $session->commit();
        }
    }


    public function testZeroRows() {
        $session = new Session($this->getSQLiteEngine());

        $session->ddl()->dropAll();
        $session->ddl()->createAll();

        $objects = $session->objects('Alchemy\tests\Language');
        $this->assertEquals(0, count($all   = $objects->all()));
        $this->assertThrows("\Exception", array($objects, 'first'));
        $this->assertThrows("\Exception", array($objects, 'one'));
    }


    public function testMultipleRows() {
        $session = new Session($this->getSQLiteEngine());

        $session->ddl()->dropAll();
        $session->ddl()->createAll();

        $lang = new Language();
        $lang->LanguageID = 10;
        $lang->ISO2Code = 'es';
        $lang->LatestChangeStamp = new DateTime("1984-01-01");
        $session->add($lang);

        $lang = new Language();
        $lang->LanguageID = 12;
        $lang->ISO2Code = 'fr';
        $lang->LatestChangeStamp = new DateTime("1984-01-01");
        $session->add($lang);

        $session->commit();

        $objects = $session->objects('Alchemy\tests\Language');
        $this->assertEquals(2, count($all   = $objects->all()));
        $this->assertEquals(1, count($first = $objects->first()));

        $this->assertInstanceOf('Alchemy\tests\Language', $first);
        $this->assertEquals(10, $first->LanguageID);
        $this->assertEquals('es', $first->ISO2Code);

        $this->assertThrows("Exception", array($objects, 'one'));
    }
}
