<?php

namespace In2code\Lux\Tests\Helper;

class BackendTester extends \Codeception\Module
{
    public function loginToBackend($I, $username = 'akellner', $password = 'akellner')
    {
        $I->amOnPage('/typo3/');
        $I->fillField('#t3-username', $username);
        $I->fillField('#t3-password', $password);
        $I->click('Login');
        $I->waitForElement('#typo3-cms-backend-backend-toolbaritems-systeminformationtoolbaritem');
    }
}
