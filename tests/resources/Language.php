<?php

namespace Alchemy\tests;
use Alchemy\orm\DataMapper;


class Language extends DataMapper {
    protected static $table_name = "Language";
    protected static $props = array(
        'LanguageID' => 'Integer(primary_key = true, auto_increment = true)',
        'ISO2Code' => 'String(2, unique = true)',
        'ParentLanguageID' => 'Foreign(self.LanguageID, null = true)',
        'SpokenLanguageID' => 'Foreign(self.LanguageID, null = true)',
        'LatestChangeStamp' => 'Timestamp',
    );

    protected static $relationships = array(
        'Files' => 'OneToMany(Alchemy\\tests\\UploadedFile, backref = "Language")',
        'Parent' => 'ManyToOne(Alchemy\\tests\\Language, backref = "Children", key = self.ParentLanguageID)',
        'SpokenLanguage' => 'OneToOne(Alchemy\\tests\\Language, backref = "WrittenLanguage", key = self.SpokenLanguageID)',
    );
}

Language::register();