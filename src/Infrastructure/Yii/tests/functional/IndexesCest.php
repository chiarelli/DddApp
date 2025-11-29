<?php

class IndexesCest
{
  public function _before(\FunctionalTester $I)
  {
    // nothing
  }

  public function testIndexesExist(\FunctionalTester $I)
  {
    $db = \Yii::$app->db;

    // Resolve table names including possible prefix
    $crpTable = ($db->tablePrefix ?? '') . 'customer_relationship_person';
    $customerTable = ($db->tablePrefix ?? '') . 'customer';

    // Check indexes on customer_relationship_person
    $rows = $db->createCommand("SHOW INDEX FROM `{$crpTable}`")->queryAll();
    $I->assertNotEmpty($rows, "No indexes found on table {$crpTable} (table may not exist or SHOW INDEX failed)");

    $keyNames = array_map(function ($r) {
      return $r['Key_name'] ?? $r['Key_name'];
    }, $rows);

    $I->assertContains('idx_crp_customer_person', $keyNames, 'Expected idx_crp_customer_person not found on ' . $crpTable);
    $I->assertContains('idx_crp_c_rel_person', $keyNames, 'Expected idx_crp_c_rel_person not found on ' . $crpTable);

    // Check index on customer.fullname
    $rowsCust = $db->createCommand("SHOW INDEX FROM `{$customerTable}`")->queryAll();
    $I->assertNotEmpty($rowsCust, "No indexes found on table {$customerTable} (table may not exist or SHOW INDEX failed)");

    $keyNamesCust = array_map(function ($r) {
      return $r['Key_name'] ?? $r['Key_name'];
    }, $rowsCust);

    $I->assertContains('idx_customer_fullname', $keyNamesCust, 'Expected idx_customer_fullname not found on ' . $customerTable);
  }
}
