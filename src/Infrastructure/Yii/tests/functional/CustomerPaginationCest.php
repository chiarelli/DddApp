<?php

class CustomerPaginationCest
{
    public function _before(\FunctionalTester $I)
    {
        // Ensure admin exists and log in
        $admin = \app\models\User::findByUsername('admin');
        $I->assertNotNull($admin, 'Admin user must exist for functional tests');
        $I->amLoggedInAs((int)$admin->id);
    }

    public function testPaginationAndFilter(\FunctionalTester $I)
    {
        $db = \Yii::$app->db;

        // Ensure the seed migration has been applied; fail the test if not.
        $migrationVersion = 'm251129_023453_seed_bulk_customers_people';
        $applied = (new \yii\db\Query())
            ->from('{{%migration}}')
            ->where(['version' => $migrationVersion])
            ->exists($db);

        $I->assertTrue($applied, "Seed migration {$migrationVersion} not applied; please run migrations before executing functional tests.");

        // Count existing SeedBulkCustomer rows; require at least 60 to proceed.
        $count = (int)$db->createCommand("SELECT COUNT(*) FROM {{%customer}} WHERE fullname LIKE 'SeedBulkCustomer %'")->queryScalar();
        $I->assertGreaterThanOrEqual(60, $count, "Expected at least 60 SeedBulkCustomer rows (migration seed); found: {$count}");

        // PageSize = 20 (default). Page 1 should contain SeedBulkCustomer 1001
        $I->amOnRoute('customer/index', ['page' => 1]);
        $I->see('Customers & Linked People', 'h1');
        $I->see('SeedBulkCustomer 1001');

        // Page 2 should contain SeedBulkCustomer 1021 (assuming 20 per page)
        $I->amOnRoute('customer/index', ['page' => 2]);
        $I->see('SeedBulkCustomer 1021');

        // Filter: search for a specific name
        $I->amOnRoute('customer/index', ['q' => 'SeedBulkCustomer 1030']);
        $I->see('SeedBulkCustomer 1030');
        // Should not see unrelated item on filtered page
        $I->dontSee('SeedBulkCustomer 1001');
    }
}
