<?php

namespace Alchemy\tests;
use Alchemy\orm\DataMapper;


class UploadedFile extends DataMapper {
    protected static $table_name = "UploadedFile";
    protected static $schema_args = array(
        'columns' => array(
            'UploadedFileID' => 'Integer(primary_key = true, auto_increment = true)',
            'LanguageID' => 'Foreign(Language.LanguageID)',
            'Folder' => 'String(40)',
            'Filename' => 'String(40)',
            'LatestChangeStamp' => 'Timestamp',
        ),
        'indexes' => array(
            'Folder_Filename' => 'UniqueKey([self.Folder, self.Filename])',
        ));
}
