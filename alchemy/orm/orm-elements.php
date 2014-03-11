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
        'rel.inverse' => 'OneFromOne',
        'rel.single' => true)));

OneToOne::define('OneFromOne', 'OneToOne', array(
    'tags' => array(
        'rel.parent' => true)));

Relationship::define_alias('OneToOne', 'OneToOne::OneToOne'); // FK side
Relationship::define_alias('OneFromOne', 'OneToOne::OneFromOne'); // PK side

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
