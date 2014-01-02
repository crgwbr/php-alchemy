<?php

namespace Alchemy\tests;
use Alchemy\orm\Session;
use Datetime;

require_once 'resources/Language.php';


class MapperTest extends BaseTest {

    public function testInsert() {
        $engine = $this->getMockBuilder('Alchemy\engine\Engine')
                       ->setConstructorArgs(array('sqlite::memory:'))
                       ->setMethods(array('execute'))
                       ->getMock();

        $engine->expects($this->once())
               ->method('execute')
               ->with($this->equalTo('INSERT INTO Alchemy_tests_Language (LanguageID, ISO2Code, LatestChangeStamp) VALUES (?, ?, ?)'));

        $session = new Session($engine);

        $lang = new Language();
        $lang->LanguageID = 10;
        $lang->ISO2Code = 'es';
        $lang->LatestChangeStamp = new DateTime("1984-01-01");

        $session->add($lang);
        $session->commit();
    }


    public function testSelect() {
        $engine = $this->getMockBuilder('Alchemy\engine\Engine')
                       ->setConstructorArgs(array('sqlite::memory:'))
                       ->setMethods(array('execute'))
                       ->getMock();

        $engine->expects($this->once())
               ->method('execute')
               ->with($this->equalTo('SELECT al1.LanguageID as LanguageID, al1.ISO2Code as ISO2Code, al1.LatestChangeStamp as LatestChangeStamp FROM Alchemy_tests_Language al1'));

        $session = new Session($engine);
        $all = $session->objects('Alchemy\tests\Language')->all();
    }
}
