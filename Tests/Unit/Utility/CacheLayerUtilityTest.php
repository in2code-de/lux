<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Controller\AnalysisController;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Tests\Helper\TestingHelper;
use In2code\Lux\Tests\Unit\Fixtures\Utility\CacheLayerUtilityFixture;
use In2code\Lux\Utility\CacheLayerUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Utility\CacheLayerUtility
 */
#[CoversClass(CacheLayerUtility::class)]
#[CoversMethod(CacheLayerUtility::class, 'getCacheLayerConfigurationByRoute')]
#[CoversMethod(CacheLayerUtility::class, 'getCachelayerConfiguration')]
#[CoversMethod(CacheLayerUtility::class, 'getCachelayerLifetimeByCacheName')]
#[CoversMethod(CacheLayerUtility::class, 'getCachelayerRoutes')]
#[CoversMethod(CacheLayerUtility::class, 'registerCacheLayers')]
class CacheLayerUtilityTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testGetPropertyFromBackendUser(): void
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
     */
    public function testGetCachelayerLifetimeByCacheName(): void
    {
        CacheLayerUtility::registerCacheLayers();
        $key = AnalysisController::class . '->dashboardAction';
        self::assertGreaterThan(0, CacheLayerUtility::getCachelayerLifetimeByCacheName($key));
    }

    /**
     * @return void
     * @throws ConfigurationException
     */
    public function testGetCacheLayerConfigurationByRoute(): void
    {
        CacheLayerUtility::registerCacheLayers();
        self::assertGreaterThan(0, count(CacheLayerUtility::getCacheLayerConfigurationByRoute('lux_LuxAnalysis')));
    }

    /**
     * @return void
     * @throws ConfigurationException
     */
    public function testGetCacheLayerConfigurationByRouteEmptyRoute(): void
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
     */
    public function testGetCacheLayerConfigurationByRouteNoRouteFound(): void
    {
        CacheLayerUtility::registerCacheLayers();
        $this->expectExceptionCode(1645176561);
        CacheLayerUtility::getCacheLayerConfigurationByRoute('foo');
    }

    public function testGetCachelayerRoutes(): void
    {
        CacheLayerUtility::registerCacheLayers();
        self::assertGreaterThan(0, count(CacheLayerUtility::getCachelayerRoutes()));
    }

    public function testGetCachelayerConfiguration(): void
    {
        CacheLayerUtility::registerCacheLayers();
        self::assertGreaterThan(0, count(CacheLayerUtilityFixture::getCachelayerConfiguration()));
    }
}
