<?php

namespace Alchemy\core\schema;
use Alchemy\core\query\Scalar;

// table

Table::define('Core', 'Core', array(
    'defaults' => array(
        'columns' => array(),
        'indexes' => array()),
    'tags' => array(
        'sql.compile' => 'Table',
        'sql.create'  => 'Table')));


// indexes

Index::define(null, null, array(
   'defaults' => array(array()),
   'tags' => array(
      'sql.compile' => 'Index')));

Index::define('UniqueKey');

Index::define('PrimaryKey');

ForeignKey::define(null, null, array(
   'defaults' => array(array(), array(),
      'ondelete' => 'restrict',
      'onupdate' => 'restrict'),
   'tags' => array(
      'sql.compile' => 'ForeignKey') ));

Index::define_alias('ForeignKey', 'ForeignKey::ForeignKey');


// columns

Column::define(null, null, array(
   'defaults' => array(
      'default' => null,
      'foreign_key' => null,
      'index' => false,
      'null' => false,
      'primary_key' => false,
      'unique' => false),
   'tags' => array(
      'sql.compile' => 'Column',
      'expr.value' => 'string'),
   'decode' => function($self, $value) {

      if (!is_null($value)) {
         settype($value, $self->getTag('expr.value'));
      }
      return $value;
   },
   'encode' => function($self, $value) {
      $type = $self->getTag('expr.value');

      if (!is_null($value)) {
         settype($value, $type);
      }

      return new Scalar($value, $type);
   } ));


// numerics

Column::define('Bool', null, array(
   'tags' => array(
      'expr.value' => 'boolean') ));


Column::define('Integer', null, array(
   'defaults' => array(11,
      'auto_increment' => false,
      'unsigned' => false),
   'tags' => array(
      'expr.value' => 'integer') ));

Column::define('TinyInt', 'Integer', array(
   'defaults' => array(4) ));

Column::define('SmallInt', 'Integer', array(
   'defaults' => array(6) ));

Column::define('MediumInt', 'Integer', array(
   'defaults' => array(8) ));

Column::define('BigInt', 'Integer', array(
   'defaults' => array(20) ));


Column::define('Float', null, array(
   'defaults' => array(23,
      'unsigned' => false) ));


Column::define('Decimal', null, array(
   'defaults' => array(5, 2) ));


// strings

Column::define('Blob');

Column::define('Binary', null, array(
   'defaults' => array(255,
      'collation' => null) ));

Column::define('String', null, array(
   'defaults' => array(255,
      'collation' => null) ));

Column::define('Char', 'String');

Column::define('Text', 'String');


// datetimes

Column::define('Date');

Column::define('Time');

Column::define('Datetime', null, array(
   'tags' => array(
      'expr.value' => 'datetime'),
   'decode' => function ($self, $value) {
      return is_null($value) ? null : new \Datetime($value);
   },
   'encode' => function ($self, $value) {
      if (is_null($value)) {
         return null;
      }

      if (!($value instanceof \Datetime)) {
         $value = $self->decode($value);
      }

      $value = $value->format('Y-m-d H:i:s');
      return new Scalar($value, 'string');
   } ));

Column::define('Timestamp', 'Datetime');


// special

Foreign::define(null, 'Column::Column');
Column::define_alias('Foreign', 'Foreign::Foreign');
