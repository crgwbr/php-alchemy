<?php

namespace Alchemy\tests;
use Alchemy\orm\DataMapper;


class UploadedFile extends DataMapper {
    protected static $props = array(
        'UploadedFileID' => 'Integer(primary_key = true, auto_increment = true)',
        'Folder' => 'String(40)',
        'Filename' => 'String(40)',
        'LatestChangeStamp' => 'Timestamp',
    );

    protected static $indexes = array(
        'Folder_Filename' => 'Unique(Folder, Filename)',
    );
}
