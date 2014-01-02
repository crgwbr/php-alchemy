<?php

// compound expressions

$expr = SQL::AND(
   SQL::OR(
      SQL::AND(
         SQL::EQ($table->StringCol, 'Blue%'),
         SQL::GT(SQL::COUNT($table->ManyRelation), 37)),
      SQL::IN($table->ManyRelation, $subquery)),
   SQL::OR(
      SQL::LE($table->IntegerCol,
         SQL::CASE($table->OtherCol)
            ->WHEN(1, 400),
            ->WHEN(2, 100),
            ->ELSE(   999)) ),
      SQL::NE($table->RowStateID,
         SQL::IF(SQL::GT(SQL::VAR('var2'), 3), ROWSTATEID_DELETED)
          ->ELIF(SQL::GT(SQL::VAR('var2'), 6), ROWSTATEID_ACTIVE)
          ->ELSE(                              ROWSTATEID_NEW)));


// Query-first style

$user = User::table();
$query = new QueryManager();

// returns Iterable(array(string, string, Address))
$aResults = $query
   ->select(array(
      'username' => $user->UserName,
      'phone'    => $user->PhoneNum,
      'address'  => $user->Address,
      'roles'    => $user->Roles))
   ->join($user->Address->Country, $user->Address->City)
   ->filter(SQL::EQ($user->Address->AddressType, 5))
   ->all();


$query = new QueryManager($session);
$query->select($JWOName = PersonName::table())
   ->filter( // defaults to a SQL::AND(A, B, C) relationship
      SQL::EQ($JWOName->PersonNameType, PERNAMETYPEID_JWOTRNSLITD)
      SQL::GT($JWOName->Person->PerPersonID, 0),
      SQL::EQ($JWOName->Person->DeletedDatetime, 0))
   ->except($query
      ->select($srcName = PersonName::table())
      ->filter(
         SQL::EQ($srcName->PersonNameType, $intTrnslitdSrcType),
         SQL::EQ($srcName->Person, $JWOName->Person)))
   ->delete();


// Table-first set-logic style

$user = User::table();

// anonymous table w/ just links to other columns/tables
$table = new Table(array(
   'username' => $user->UserName,   // column
   'phone'    => $user->PhoneNum,   // column
   'address'  => $user->Address,    // table 1:1
   'roles'    => $user->Roles));    // table 1:M

$query = $table
   ->join($user->Address->Country, $user->Address->City)
   ->filter(SQL::EQ($user->Address->AddressType, 5));

// returns Iterable(array(string, string, Address, Iterable(UserRole)))
$aResults = $session->select($query)->all();


$JWOName = PersonName::table(); // two tables with distinct aliases, etc
$srcName = PersonName::table();

$query = $JWOName->filter(
      SQL::EQ($JWOName->PersonNameTypeID, PERNAMETYPEID_JWOTRNSLITD)
      SQL::GT($JWOName->Person->PerPersonID, 0),
      SQL::EQ($JWOName->Person->DeletedDatetime, 0))
   ->except($srcName->filter(
      SQL::EQ($srcName->PersonNameTypeID, $intTrnslitdSrcType)),
      SQL::EQ($srcName->Person, $JWOName->Person));

$session->delete($query);
