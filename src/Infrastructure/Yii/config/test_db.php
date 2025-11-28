<?php
$db = require __DIR__ . '/db.php';
// test database! Important not to run tests on production or development databases
$db = array_merge(
  $db,
  [
    'dsn' => 'mysql:host=db;dbname=yii2',
    'username' => 'yii2',
    'password' => 'yii2',
    'charset' => 'utf8',
  ]
);

return $db;
