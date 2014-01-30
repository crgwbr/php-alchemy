<?php

namespace Alchemy\expression;


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
