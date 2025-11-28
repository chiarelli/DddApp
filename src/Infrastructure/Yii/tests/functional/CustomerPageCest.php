<?php

class CustomerPageCest
{
  public function _before(\FunctionalTester $I)
  {
    // Log in as seeded admin user so protected routes are accessible.
    $admin = \app\models\User::findByUsername('admin');
    $I->assertNotNull($admin, 'Admin user must exist for functional tests');
    $I->amLoggedInAs((int)$admin->id);
  }

  /**
   * Verifies the /customer/index page loads and shows expected structure and seeded values.
   */
  public function viewCustomerIndex(\FunctionalTester $I)
  {
    $I->amOnRoute('customer/index');

    // Page header
    $I->see('Customers & Linked People', 'h1');

    // Table headers (as rendered by the view)
    $I->see('Primeiro Nome');
    $I->see('Relação');
    $I->see('Birthdate');
    $I->see('Age');

    // Check seeded customer and linked person/relationship are present
    $I->see('John Smith');
    $I->see('Emma');
    $I->see('daughter');

    // Ensure there is at least one table of linked people
    $I->seeElement('table.table');
  }

  /**
   * Additional sanity check: ensure multiple customers are listed (seed created 5).
   */
  public function listContainsMultipleCustomers(\FunctionalTester $I)
  {
    $I->amOnRoute('customer/index');

    // The seed inserts several customers; check for more than one known name
    $I->see('John Smith');
    $I->see('Mary Johnson');
    $I->see('Carlos Oliveira');
  }
}
