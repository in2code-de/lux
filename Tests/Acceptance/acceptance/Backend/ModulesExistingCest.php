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
        $I->scrollTo('//span[contains(@class, "modulemenu-name") and text()="LUX"]');

        $luxButton = '//button[@data-modulemenu-identifier="lux_module"]';
        $isExpanded = $I->grabAttributeFrom($luxButton, 'aria-expanded');

        if ($isExpanded !== 'true') {
            $I->click($luxButton);
        }

        $I->wait(1);

        $I->see('LUX');
        $I->see('Analysis');
        $I->see('Leads');
        $I->see('Campaigns');
    }
}
