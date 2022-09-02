<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Controller\AnalysisController;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Tests\Helper\TestingHelper;
use In2code\Lux\Tests\Unit\Fixtures\Utility\CacheLayerUtilityFixture;
use In2code\Lux\Utility\CacheLayerUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class CacheLayerUtilityTest
 * @coversDefaultClass \In2code\Lux\Utility\CacheLayerUtility
 */
class CacheLayerUtilityTest extends UnitTestCase
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
     * @covers ::registerCacheLayers
     */
    public function testGetPropertyFromBackendUser()
    {
        CacheLayerUtility::registerCacheLayers();
        self::assertGreaterThan(0, count($GLOBALS['TYPO3_CONF_VARS']['EXT']['lux']['cachelayer']));
        $first = current($GLOBALS['TYPO3_CONF_VARS']['EXT']['lux']['cachelayer']);
        self::assertArrayHasKey('lifetime', $first);
        self::assertArrayHasKey('route', $first);
        self::assertArrayHasKey('arguments', $first);
        self::assertArrayHasKey('multiple', $first);
    }

    /**
     * @return void
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     * @covers ::getCachelayerLifetimeByCacheName
     */
    public function testGetCachelayerLifetimeByCacheName()
    {
        CacheLayerUtility::registerCacheLayers();
        $key = AnalysisController::class . '->dashboardAction';
        self::assertGreaterThan(0, CacheLayerUtility::getCachelayerLifetimeByCacheName($key));
    }

    /**
     * @return void
     * @throws ConfigurationException
     * @covers ::getCacheLayerConfigurationByRoute
     */
    public function testGetCacheLayerConfigurationByRoute()
    {
        CacheLayerUtility::registerCacheLayers();
        self::assertGreaterThan(0, count(CacheLayerUtility::getCacheLayerConfigurationByRoute('lux_LuxAnalysis')));
    }

    /**
     * @return void
     * @throws ConfigurationException
     * @covers ::getCacheLayerConfigurationByRoute
     */
    public function testGetCacheLayerConfigurationByRouteEmptyRoute()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['lux']['cachelayer'][AnalysisController::class . '->dashboardAction'] = [
            'lifetime' => 86400,
            'arguments' => [],
            'multiple' => false,
        ];
        $this->expectExceptionCode(1645176511);
        CacheLayerUtility::getCacheLayerConfigurationByRoute('foo');
    }

    /**
     * @return void
     * @throws ConfigurationException
     * @covers ::getCacheLayerConfigurationByRoute
     */
    public function testGetCacheLayerConfigurationByRouteNoRouteFound()
    {
        CacheLayerUtility::registerCacheLayers();
        $this->expectExceptionCode(1645176561);
        CacheLayerUtility::getCacheLayerConfigurationByRoute('foo');
    }

    /**
     * @return void
     * @covers ::getCachelayerRoutes
     */
    public function testGetCachelayerRoutes()
    {
        CacheLayerUtility::registerCacheLayers();
        self::assertGreaterThan(0, count(CacheLayerUtility::getCachelayerRoutes()));
    }

    /**
     * @return void
     * @covers ::getCachelayerConfiguration
     */
    public function testGetCachelayerConfiguration()
    {
        CacheLayerUtility::registerCacheLayers();
        self::assertGreaterThan(0, count(CacheLayerUtilityFixture::getCachelayerConfiguration()));
    }
}
