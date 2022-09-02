<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Tests\Helper\TestingHelper;
use In2code\Lux\Tests\Unit\Fixtures\Utility\BackendUtilityFixture;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Class BackendUserUtilityTest
 * @coversDefaultClass \In2code\Lux\Utility\BackendUtility
 */
class BackendUtilityTest extends UnitTestCase
{
    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    /**
     * @return void
     */
    public function setUp()
    {
        TestingHelper::setDefaultConstants();
    }

    /**
     * @return void
     * @covers ::getPropertyFromBackendUser
     */
    public function testGetPropertyFromBackendUser()
    {
        $uidTest = rand();
        $this->initializeAuthentication(['uid' => $uidTest], ['username' => 'lux_user']);
        self::assertSame($uidTest, BackendUtilityFixture::getPropertyFromBackendUser());
        self::assertSame('lux_user', BackendUtilityFixture::getPropertyFromBackendUser('username'));
        self::assertNotSame('', BackendUtilityFixture::getPropertyFromBackendUser('username'));
        self::assertNotSame('', BackendUtilityFixture::getPropertyFromBackendUser());
    }

    /**
     * @return void
     * @covers ::isAdministrator
     */
    public function testIsAdministrator()
    {
        self::assertFalse(BackendUtilityFixture::isAdministrator());
        $this->initializeAuthentication(['uid' => 123], ['username' => 'lux_user'], ['admin' => 1]);
        self::assertTrue(BackendUtilityFixture::isAdministrator());
    }

    /**
     * @return void
     */
    public function testGetSessionValue()
    {
        $this->initializeAuthentication(['uid' => 123], ['username' => 'lux_user'], ['admin' => 1]);
        self::assertSame([], BackendUtilityFixture::getSessionValue('foo', 'bar', 'baz'));
    }

    /**
     * @return void
     * @covers ::getBackendUserAuthentication
     */
    public function testGetBackendUserAuthentication()
    {
        $this->initializeAuthentication(['uid' => 123], ['username' => 'lux_user']);
        self::assertTrue(
            is_a(BackendUtilityFixture::getBackendUserAuthentication(), BackendUserAuthentication::class)
        );
    }

    /**
     * @param array ...$properties
     * @SuppressWarnings(PHPMD.Superglobals)
     * @return void
     */
    protected function initializeAuthentication(array ...$properties)
    {
        $authentication = new BackendUserAuthentication();
        foreach ($properties as $property) {
            $authentication->user[array_keys($property)[0]] = array_values($property)[0];
        }
        $GLOBALS['BE_USER'] = $authentication;
    }
}
