<?php
namespace ApplicationTest;
use \AcceptanceTester;

class HomePageCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->am('guest user');
        $I->wantTo('Check that the home page renders as it should');
        $I->amGoingTo("Load the home page");
        $I->amOnPage('/');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function testHomePage(AcceptanceTester $I)
    {
        $I->seeInCurrentUrl('/');
        $I->dontSeeElement('#create-link');
    }
}
