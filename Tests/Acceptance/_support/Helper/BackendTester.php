<?php

namespace In2code\Lux\Tests\Helper;

use In2code\Lux\Tests\Utility\ViewPortUtility;

class BackendTester extends \Codeception\Module
{
    protected $backendConfig = [
        'backendCookieValue' => '',
    ];

    /**
     * @return string
     */
    public function getBackendCookieValue()
    {
        return $this->backendConfig['backendCookieValue'];
    }

    /**
     * @param string $backendCookieValue
     */
    public function setBackendCookieValue(string $backendCookieValue)
    {
        $this->backendConfig['backendCookieValue'] = $backendCookieValue;
    }

    /**
     * @param $tester
     */
    public function loginToBackend($tester)
    {
        if (empty($_ENV['BE_USER']) || empty($_ENV['BE_PASSWORD'])) {
            $this->getBackendUserAndPassword();
        }

        $tester->amOnPage('/typo3');

        $tester->fillField('#t3-username', $_ENV['BE_USER']);
        $tester->fillField('#t3-password', $_ENV['BE_PASSWORD']);

        $tester->click('#t3-login-submit');

        $cookie = $tester->grabCookie('be_typo_user');
        $this->setBackendCookieValue($cookie);
    }

    /**
     * @return void
     */
    public function getBackendUserAndPassword(): void
    {
        echo "\r\n";
        echo 'Backend Login needed!';
        echo "\r\n";

        echo 'Backend User:';
        $_ENV['BE_USER'] = trim(fgets(STDIN));

        echo 'Backend User Password:';
        system('stty -echo');

        $_ENV['BE_PASSWORD'] = trim(fgets(STDIN));

        system('stty echo');

        echo "\r\n";
        echo 'Backend User and Password set';
    }

    /**
     * @param $tester
     */
    public function backendLogout($tester)
    {
        $tester->amOnPage('/typo3');
        $tester->resizeWindow(ViewPortUtility::DESKTOP, 1080);

        if (!empty($this->getBackendCookieValue())) {
            $tester->setCookie('be_typo_user', $this->getBackendCookieValue());

            $this->setBackendCookieValue('');
        } else {
            var_dump('ALREADY LOGGED OUT!');
        }
    }
}
