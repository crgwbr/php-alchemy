<?php

namespace Alchemy\tests;
use Alchemy\orm\DataMapper;


class Tree extends DataMapper {
    protected static $table_name = "Tree";
    protected static $schema_args = array(
        'columns' => array(
            'TreeID' => 'Integer(primary_key = true, auto_increment = true)',
            'ParentTreeID' => 'Foreign(self.TreeID, null = true)',
        ),
        'relationships' => array(
            'Parent' => 'ManyToOne(Alchemy\\tests\\Tree, inverse = "Children", key = self.ParentTreeID)',
        ));
}
