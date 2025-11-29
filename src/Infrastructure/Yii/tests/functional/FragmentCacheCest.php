<?php

class FragmentCacheCest
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

    public function testFragmentCacheHitsAndGranularInvalidation(\FunctionalTester $I)
    {
        // Warm page 1 and capture initial timestamps for two customers on page 1 (1001 and 1002)
        $I->amOnRoute('customer/index', ['page' => 1]);
        $I->see('Customers & Linked People', 'h1');
        $html1 = $I->grabPageSource();

        $ts1001_first = $this->extractCacheTsForId($html1, 1001);
        $ts1002_first = $this->extractCacheTsForId($html1, 1002);

        $I->assertNotEmpty($ts1001_first, 'Expected cached timestamp for customer 1001 on first render');
        $I->assertNotEmpty($ts1002_first, 'Expected cached timestamp for customer 1002 on first render');

        // Repeat same page: timestamps should be identical (cache hit)
        $I->amOnRoute('customer/index', ['page' => 1]);
        $html2 = $I->grabPageSource();

        $ts1001_second = $this->extractCacheTsForId($html2, 1001);
        $ts1002_second = $this->extractCacheTsForId($html2, 1002);

        $I->assertSame($ts1001_first, $ts1001_second, 'Customer 1001 block should be served from fragment cache');
        $I->assertSame($ts1002_first, $ts1002_second, 'Customer 1002 block should be served from fragment cache');

        // Update only customer 1001 -> should invalidate only its fragment (due to tags customer_1001/link_customer_1001)
        $cust = \app\models\Customer::findOne(1001);
        $I->assertNotNull($cust, 'Seed customer 1001 should exist');
        $cust->fullname = 'SeedBulkCustomer 1001 FRAG UPDATED';
        $cust->save(false);

        // Reload page 1: customer 1001 timestamp must change, 1002 must remain the same
        $I->amOnRoute('customer/index', ['page' => 1]);
        $html3 = $I->grabPageSource();

        $ts1001_third = $this->extractCacheTsForId($html3, 1001);
        $ts1002_third = $this->extractCacheTsForId($html3, 1002);

        $I->assertNotSame($ts1001_first, $ts1001_third, 'Customer 1001 block should be re-rendered after invalidation');
        $I->assertSame($ts1002_second, $ts1002_third, 'Customer 1002 block should remain cached (unchanged)');
    }

    private function extractCacheTsForId(string $html, int $id): ?string
    {
        // Matches: <!-- customer_card_cached_at: 1700000000 :id=1001 -->
        $pattern = sprintf('/<!--\s*customer_card_cached_at:\s*(\d+)\s*:id=%d\s*-->/', $id);
        if (preg_match($pattern, $html, $m)) {
            return $m[1];
        }
        return null;
    }
}