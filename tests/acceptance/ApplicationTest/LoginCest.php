<?php
namespace ApplicationTest;
use \AcceptanceTester;

class LoginCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->am('guest user');
        $I->wantTo('Login');
        $I->amGoingTo("Load the home page");
        $I->amOnPage('/');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
        $I->seeInCurrentUrl('/');
        $I->seeElement("#navbar-first-collapse");
        $I->click('Non connecté');
        $I->click('#openloginwindow');
        $I->waitForElementVisible('#loginwindow');
        $I->submitForm('#loginwindow form', array(
            'identity' => 'admin',
            'credential' => 'adminadmin'
        ));
        $I->waitForElementVisible('#create-link');
        $I->seeElement('#create-link');
    }
}
