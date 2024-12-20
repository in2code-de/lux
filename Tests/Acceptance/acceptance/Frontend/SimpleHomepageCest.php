<?php

namespace In2code\Lux\Tests\Acceptance\Frontend;

use In2code\Lux\Tests\AcceptanceTester;

class SimpleHomepageCest
{
    public function checkHomepage(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Marketing');
    }
}
