<?php

namespace Alchemy\tests;
use Alchemy\orm\Session;
use Datetime;

require_once 'resources/Language.php';


class SessionIntegrationTest extends BaseTest {

    public function testSQLiteModelRoundTrip() {
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
            $all = $session->objects('Alchemy\tests\Language')->all();
            $this->assertEquals(1, count($all));

            $lang = $all[0];

            $this->assertEquals(1, $lang->LanguageID);
            $this->assertEquals('es', $lang->ISO2Code);
            $this->assertEquals('1984-01-01', $lang->LatestChangeStamp->format('Y-m-d'));
        }
    }
}
