<?php

use Chiarelli\DddApp\Infrastructure\Repository\YiiCustomerReadRepository;
use app\models\Customer as CustomerAR;
use app\models\Person as PersonAR;
use app\models\CustomerRelationshipPerson as CRPAR;

class YiiCustomerReadRepositoryCest
{
    public function _before(\FunctionalTester $I)
    {
        // nothing
    }

    public function testFindAllWithPeople(\FunctionalTester $I)
    {
        // Create Customer
        $cust = new CustomerAR();
        $cust->fullname = 'Functional Customer';
        $cust->birthdate = '1980-01-01';
        $cust->save(false);

        // Create Person
        $person = new PersonAR();
        $person->first_name = 'Jane';
        $person->birthdate = '2000-06-15';
        $person->save(false);

        // Link via pivot
        $crp = new CRPAR();
        $crp->customer_id = (int)$cust->id;
        $crp->person_id = (int)$person->id;
        $crp->relationship = 'daughter';
        $crp->save(false);

        // Execute repository
        $repo = new YiiCustomerReadRepository();
        $rows = $repo->findAllWithPeople();

        $I->assertNotEmpty($rows, 'Repository should return rows');

        // Find our customer row
        $found = false;
        foreach ($rows as $row) {
            $customer = $row['customer'];
            if ($customer->getId() === (int)$cust->id) {
                $I->assertSame('Functional Customer', $customer->getFullName());
                $I->assertCount(1, $row['people']);
                $pair = $row['people'][0];
                $personDomain = $pair['person'];
                $I->assertSame('Jane', $personDomain->getFirstName());
                $I->assertSame('daughter', $pair['relationship']);
                $found = true;
            }
        }

        $I->assertTrue($found, 'Inserted customer should be present in repository result');
    }
}