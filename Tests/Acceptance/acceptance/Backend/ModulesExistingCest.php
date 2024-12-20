<?php

namespace In2code\Lux\Tests\Acceptance\Backend;

use In2code\Lux\Tests\AcceptanceTester;

class ModulesExistingCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->loginToBackend($I);
    }

    public function loginToBackendSuccessfully(AcceptanceTester $I)
    {
        $I->see('LUX');
        $I->see('Analyse');
        $I->see('Leads');
        $I->see('Kampagnen');
    }
}
