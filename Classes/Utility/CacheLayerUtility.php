<?php
declare(strict_types = 1);
namespace In2code\Lux\Utility;

use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\UnexpectedValueException;

/**
 * Class ConfigurationUtility
 */
class CacheLayerUtility
{
    /**
     * @return void
     */
    public static function registerCacheLayers(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['lux']['cachelayer'][\In2code\Lux\Controller\AnalysisController::class . '->dashboardAction'] = [
            'lifetime' => 86400,
            'class' => \In2code\Lux\Domain\Cache\AnalysisDashboard::class,
            'identifier' => false
        ];
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['lux']['cachelayer'][\In2code\Lux\Controller\LeadController::class . '->dashboardAction'] = [
            'lifetime' => 86400,
            'class' => \In2code\Lux\Domain\Cache\LeadDashboard::class,
            'identifier' => false
        ];
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['lux']['cachelayer'][\In2code\Lux\Hooks\PageOverview::class . '->getArguments'] = [
            'lifetime' => 86400,
            'class' => \In2code\Lux\Domain\Cache\PageOverview::class,
            'identifier' => 'pageIdentifier'
        ];
    }

    /**
     * @param string $cacheName class->method
     * @return string
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     */
    public static function getCachelayerClassByCacheName(string $cacheName): string
    {
        $configuration = self::getCachelayerConfiguration();
        if (isset($configuration[$cacheName]) === false) {
            throw new ConfigurationException('class ' . $cacheName . ' is not registered', 1636367507);
        }
        if (isset($configuration[$cacheName]['class']) === false) {
            throw new UnexpectedValueException('No class given in cachelayer configuration', 1636367728);
        }
        return $configuration[$cacheName]['class'];
    }

    /**
     * @param string $cacheName class->method
     * @return int
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     */
    public static function getCachelayerLifetimeByCacheName(string $cacheName): int
    {
        $configuration = self::getCachelayerConfiguration();
        if (isset($configuration[$cacheName]) === false) {
            throw new ConfigurationException('class ' . $cacheName . ' is not registered', 1636367764);
        }
        if (isset($configuration[$cacheName]['lifetime']) === false) {
            throw new UnexpectedValueException('No lifetime given in cachelayer configuration', 1636367766);
        }
        return $configuration[$cacheName]['lifetime'];
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getCachelayerConfiguration(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXT']['lux']['cachelayer'] ?? [];
    }
}
