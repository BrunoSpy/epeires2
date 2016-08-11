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
        $I->submitForm('#loginwindow form', array('user' => array(
            'identity' => 'admin',
            'credential' => 'adminadmin'
        )));
        $I->wait(10);//wait for the page to reload
        $I->seeElement('#create-evt');
    }
}