<?php

namespace Alchemy\tests;
use Alchemy\orm\DataMapper;


class Language extends DataMapper {
    protected static $table_name = "Language";
    protected static $schema_args = array(
        'columns' => array(
            'LanguageID' => 'Integer(primary_key = true, auto_increment = true)',
            'ISO2Code' => 'String(2, unique = true)',
            'ParentLanguageID' => 'Foreign(self.LanguageID, null = true)',
            'SpokenLanguageID' => 'Foreign(self.LanguageID, null = true)',
            'LatestChangeStamp' => 'Timestamp',
        ),
        'relationships' => array(
            'Files' => 'OneToMany(UploadedFile, inverse = "Language")',
            'Parent' => 'ManyToOne(Language, inverse = "Children", key = self.ParentLanguageID)',
            'SpokenLanguage' => 'OneToOne(Language, inverse = "WrittenLanguage", key = self.SpokenLanguageID)'
        ));
}
