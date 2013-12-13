<?php

namespace Alchemy\tests;
use Alchemy\engine\Engine;
use Alchemy\orm\Session;
use Datetime;

require_once 'resources/Language.php';


class SessionTest extends BaseTest {

    public function testCreate() {
        $engine = $this->getMockBuilder('Alchemy\engine\Engine')
                       ->setConstructorArgs(array('sqlite::memory:'))
                       ->setMethods(array('execute'))
                       ->getMock();

        $engine->expects($this->once())
               ->method('execute')
               ->with($this->equalTo('CREATE TABLE IF NOT EXISTS Alchemy_tests_Language (LanguageID INT(11) NOT NULL, ISO2Code VARCHAR(255) NOT NULL, LatestChangeStamp TIMESTAMP NOT NULL);'));

        $session = new Session($engine);
        $session->ddl()->createAll();
    }
}
