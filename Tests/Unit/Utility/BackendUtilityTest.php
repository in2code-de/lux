<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Tests\Helper\TestingHelper;
use In2code\Lux\Tests\Unit\Fixtures\Utility\BackendUtilityFixture;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Utility\BackendUtility
 */
class BackendUtilityTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    /**
     * @return void
     * @covers ::getPropertyFromBackendUser
     */
    public function testGetPropertyFromBackendUser(): void
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
    public function testIsAdministrator(): void
    {
        self::assertFalse(BackendUtilityFixture::isAdministrator());
        $this->initializeAuthentication(['uid' => 123], ['username' => 'lux_user'], ['admin' => 1]);
        self::assertTrue(BackendUtilityFixture::isAdministrator());
    }

    /**
     * @return void
     * @covers ::getSessionValue
     */
    public function testGetSessionValue(): void
    {
        $this->initializeAuthentication(['uid' => 123], ['username' => 'lux_user'], ['admin' => 1]);
        self::assertSame([], BackendUtilityFixture::getSessionValue('foo', 'bar', 'baz'));
    }

    /**
     * @return void
     * @covers ::getBackendUserAuthentication
     */
    public function testGetBackendUserAuthentication(): void
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
    protected function initializeAuthentication(array ...$properties): void
    {
        $authentication = new BackendUserAuthentication();
        foreach ($properties as $property) {
            $authentication->user[array_keys($property)[0]] = array_values($property)[0];
        }
        $GLOBALS['BE_USER'] = $authentication;
    }
}
