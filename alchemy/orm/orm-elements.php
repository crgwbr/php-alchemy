<?php

namespace Alchemy\orm;
use Alchemy\core\query\Query;
use Alchemy\core\schema\Table;


// query

ORMQuery::define('Select', 'Query::Select');
Query::define_alias('ORM', 'ORMQuery::Select');


// table

ORMTable::define(null, 'Table::Core', array(
    'defaults' => array(
        'class' => '',
        'relationships' => array())));

Table::define_alias('ORM', 'ORMTable::ORMTable');


// relationships

Relationship::define('Null', 'Null', array(
    'defaults' => array(null,
        'inverse' => null,
        'key' => ''),
    'tags' => array(
        'rel.inverse' => 'Relationship')));

OneToOne::define(null, 'Relationship::Null', array(
    'tags' => array(
        'rel.inverse' => 'OneToOne',
        'rel.single' => true)));

Relationship::define_alias('OneToOne', 'OneToOne::OneToOne');

OneToMany::define(null, 'Relationship::Null', array(
    'tags' => array(
        'rel.inverse' => 'ManyToOne',
        'rel.parent' => true)));

Relationship::define_alias('OneToMany', 'OneToMany::OneToMany');

ManyToOne::define(null, 'Relationship::Null', array(
    'tags' => array(
        'rel.inverse' => 'OneToMany',
        'rel.single' => true)));

Relationship::define_alias('ManyToOne', 'ManyToOne::ManyToOne');

/*ManyVia::define(null, 'Relationship::Null', array(
    'defaults' => array(
        'keys' => array()),
    'tags' => array(
        //'rel.inverse' => 'OneToMany',
        'rel.parent' => true)));

Relationship::define_alias('ManyVia', 'ManyVia::ManyVia');*/
