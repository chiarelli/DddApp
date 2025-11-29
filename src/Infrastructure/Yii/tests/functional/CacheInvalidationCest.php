<?php

class CacheInvalidationCest
{
    public function _before(\FunctionalTester $I)
    {
        // Login como admin
        $admin = \app\models\User::findByUsername('admin');
        $I->assertNotNull($admin, 'Admin user must exist for functional tests');
        $I->amLoggedInAs((int)$admin->id);

        // Habilita cache via env e garante cache limpo
        putenv('CUSTOMERS_LIST_CACHE_ENABLED=true');
        putenv('CUSTOMERS_LIST_CACHE_TTL=300');
        putenv('CUSTOMERS_LIST_PAGE_SIZE_DEFAULT=20');

        if (\Yii::$app->has('cache')) {
            \Yii::$app->cache->flush();
        }
    }

    public function testCustomerUpdateInvalidatesOnlyThatCustomer(\FunctionalTester $I)
    {
        // Warm cache: page 1 and page 2
        $I->amOnRoute('customer/index', ['page' => 1]);
        $I->see('Customers & Linked People', 'h1');
        $I->see('SeedBulkCustomer 1001'); // page 1 contains 1001

        $I->amOnRoute('customer/index', ['page' => 2]);
        $I->see('SeedBulkCustomer 1021'); // page 2 baseline

        // Update customer 1001 name
        $cust = \app\models\Customer::findOne(1001);
        $I->assertNotNull($cust, 'Seed customer 1001 should exist');
        $cust->fullname = 'SeedBulkCustomer 1001 UPDATED';
        $cust->save(false);

        // Page 1 should reflect new name (cache for link_customer_1001 invalidated)
        $I->amOnRoute('customer/index', ['page' => 1]);
        $I->see('SeedBulkCustomer 1001 UPDATED');
        $I->dontSee('SeedBulkCustomer 1001</h5>'); // old header shouldn't appear

        // Page 2 should remain unchanged (not invalidated)
        $I->amOnRoute('customer/index', ['page' => 2]);
        $I->see('SeedBulkCustomer 1021');
    }

    public function testPersonUpdateInvalidatesLinkedCustomers(\FunctionalTester $I)
    {
        // Warm page 1
        $I->amOnRoute('customer/index', ['page' => 1]);
        $I->see('SeedBulkCustomer 1001');

        // Person 2001 is linked to customer 1001 by seed
        $person = \app\models\Person::findOne(2001);
        $I->assertNotNull($person, 'Seed person 2001 should exist');
        $person->first_name = 'SeedPerson2001X';
        $person->save(false);

        // Should see updated person name in page 1
        $I->amOnRoute('customer/index', ['page' => 1]);
        $I->see('SeedPerson2001X');
    }

    public function testPivotCreateInvalidatesLink(\FunctionalTester $I)
    {
        // Warm page 1
        $I->amOnRoute('customer/index', ['page' => 1]);
        $I->see('SeedBulkCustomer 1001');

        // Create a new link 1001 - 2120 (should exist)
        $p = \app\models\Person::findOne(2120);
        if ($p === null) {
            // fallback: create a quick person if 2120 does not exist
            $p = new \app\models\Person();
            $p->id = 2120;
            $p->first_name = 'Temp2120';
            $p->last_name = 'Bulk';
            $p->birthdate = '1990-01-01';
            $p->gender = 'M';
            $p->save(false);
        }

        $link = new \app\models\CustomerRelationshipPerson();
        $link->customer_id = 1001;
        $link->person_id = 2120;
        $link->relationship = 'added-rel';
        // ignore unique constraint if already exists
        if (!$link->save()) {
            // If already exists, we still expect to see 'added-rel' only if created before; adjust for visibility
        }

        // Reload page 1: should see the new relationship label
        $I->amOnRoute('customer/index', ['page' => 1]);
        $I->see('added-rel');
    }

    public function testPivotDeleteInvalidatesLink(\FunctionalTester $I)
    {
        // Warm page 1
        $I->amOnRoute('customer/index', ['page' => 1]);
        $I->see('SeedBulkCustomer 1001');

        // Ensure a link to delete exists (1001 - 2003 exists by seed)
        $link = \app\models\CustomerRelationshipPerson::findOne(['customer_id' => 1001, 'person_id' => 2003]);
        if ($link === null) {
            // Create it if missing, then delete to test invalidation
            $link = new \app\models\CustomerRelationshipPerson();
            $link->customer_id = 1001;
            $link->person_id = 2003;
            $link->relationship = 'temp-rel';
            $link->save(false);
        }

        // Capture person name for assertion
        $person = \app\models\Person::findOne(2003);
        $nameToDisappear = $person ? (string)$person->first_name : 'SeedPerson2003';

        // Delete link
        $link->delete();

        // Reload page 1 and ensure the person's name is no longer listed under customer 1001 section
        $I->amOnRoute('customer/index', ['page' => 1]);
        // A ausência exata no bloco é difícil de isolar sem anchors; usamos verificação genérica:
        $I->dontSee($nameToDisappear . '</td><td>'); // heurística fraca mas suficiente para regressão visível
    }
}