<?php

use Alchemy\orm\DataMapper;
use Alchemy\engine\Engine;
use Alchemy\orm\Session;


class Language extends DataMapper {
    protected static $props = array(
        'LanguageID' => 'Integer(primary_key = true)',
        'ISO2Code' => 'String(2, unique = true)',
        'LatestChangeStamp' => 'Timestamp',
    );
}


class MapperTest extends BaseTest {

    public function testMapper() {
        $engine = new Engine('sqlite::memory:');
        $session = new Session($engine);
        $session->ddl()->createAll();

        $lang = new Language();
        $lang->LanguageID = 10;
        $lang->ISO2Code = 'es';
        $lang->LatestChangeStamp = new DateTime("1984-01-01");

        $session->add($lang);
        $session->commit();

        $session = new Session($engine);
        $all = $session->objects('Language')->all();

        $this->assertEquals(1, count($all));

        $lang = $all[0];
        $this->assertEquals(10, $lang->LanguageID);
        $this->assertEquals('es', $lang->ISO2Code);
        $this->assertEquals('1984-01-01', $lang->LatestChangeStamp->format('Y-m-d'));
    }
}
