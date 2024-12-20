<?php

namespace In2code\Lux\Tests\Acceptance;

use In2code\Lux\Tests\AcceptanceTester;

class FirstCest
{
    public function checkHomepage(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Marketing');
    }
}
