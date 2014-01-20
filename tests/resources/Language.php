<?php

namespace Alchemy\tests;
use Alchemy\orm\DataMapper;


class Language extends DataMapper {
    protected static $props = array(
        'LanguageID' => 'Integer(primary_key = true, auto_increment = true)',
        'ISO2Code' => 'String(2, unique = true)',
        'FallbackLanguageID' => 'ForeignKey("self.LanguageID", null = true)',
        'LatestChangeStamp' => 'Timestamp',
    );
}
