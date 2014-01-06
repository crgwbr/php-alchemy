<?php

namespace Alchemy\tests;
use Alchemy\orm\Session;
use Datetime;


class MapperTest extends BaseTest {

    public function testInsert() {
        $result = $this->getMockBuilder('Alchemy\engine\ResultSet')
                       ->disableOriginalConstructor()
                       ->setMethods(array('lastInsertID'))
                       ->getMock();

        $result->expects($this->once())
               ->method('lastInsertID')
               ->will($this->returnValue(1234));

        $engine = $this->getMockBuilder('Alchemy\engine\Engine')
                       ->setConstructorArgs(array('sqlite::memory:'))
                       ->setMethods(array('execute'))
                       ->getMock();

        $engine->expects($this->once())
               ->method('execute')
               ->with($this->equalTo('INSERT INTO Alchemy_tests_Language (ISO2Code, LatestChangeStamp) VALUES (:p0, :p1)'))
               ->will($this->returnValue($result));

        $session = new Session($engine);

        $lang = new Language();
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
