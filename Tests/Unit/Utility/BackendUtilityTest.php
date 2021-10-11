<?php
namespace In2code\Lux\Tests\Unit\Utility;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use In2code\Lux\Tests\Helper\TestingHelper;
use In2code\Lux\Tests\Unit\Fixtures\Utility\BackendUtilityFixture;
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
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getPropertyFromBackendUser
     */
    public function testGetPropertyFromBackendUser()
    {
        $uidTest = rand();
        $this->initializeAuthentication(['uid' => $uidTest], ['username' => 'lux_user']);
        $this->assertSame($uidTest, BackendUtilityFixture::getPropertyFromBackendUser());
        $this->assertSame('lux_user', BackendUtilityFixture::getPropertyFromBackendUser('username'));
        $this->assertNotSame('', BackendUtilityFixture::getPropertyFromBackendUser('username'));
        $this->assertNotSame('', BackendUtilityFixture::getPropertyFromBackendUser('uid'));
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getBackendUserAuthentication
     */
    public function testGetBackendUserAuthentication()
    {
        $this->initializeAuthentication(['uid' => 123], ['username' => 'lux_user']);
        $this->assertTrue(
            is_a(BackendUtilityFixture::getBackendUserAuthentication(), BackendUserAuthentication::class)
        );
    }

    /**
     * @param array ...$properties
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
