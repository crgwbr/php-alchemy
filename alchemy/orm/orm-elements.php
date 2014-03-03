<?php

namespace Alchemy\orm;
use Alchemy\core\query\Query;
use Alchemy\core\schema\Table;


// query

ORMQuery::define(null, 'Query::Core');
Query::define_alias('ORM', 'ORMQuery::ORMQuery');


// table

ORMTable::define(null, 'Table::Core', array(
    'defaults' => array(
        'class' => '',
        'relationships' => array())));

Table::define_alias('ORM', 'ORMTable::ORMTable');


// relationships

Relationship::define('Null', 'Null', array(
    'defaults' => array(null,
        'backref' => '',
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
