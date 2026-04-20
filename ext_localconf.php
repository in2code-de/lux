<?php

use In2code\Lux\Controller\FrontendController;
use In2code\Lux\Domain\Cache\CacheLayer;
use In2code\Lux\Domain\Cache\RateLimiterCache;
use In2code\Lux\Domain\Service\Image\CompanyImageService;
use In2code\Lux\Domain\Service\Image\VisitorImageService;
use In2code\Lux\Utility\CacheHashUtility;
use In2code\Lux\Utility\CacheLayerUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die();

/**
 * Include Frontend Plugins
 */
ExtensionUtility::configurePlugin(
    'Lux',
    'Fe',
    [FrontendController::class => 'dispatchRequest']
);
ExtensionUtility::configurePlugin(
    'Lux',
    'Email4link',
    [FrontendController::class => 'email4link']
);
ExtensionUtility::configurePlugin(
    'Lux',
    'Pi1',
    [FrontendController::class => 'trackingOptOut'],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

/**
 * CK editor configuration
 */
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['lux'] = 'EXT:lux/Configuration/Yaml/CkEditor.yaml';

/**
 * Fluid Namespace
 */
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['lux'][] = 'In2code\Lux\ViewHelpers';

/**
 * Email templates
 */
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][1762935800] =
    'EXT:lux/Resources/Private/Templates/Mail/';

/**
 * Caching framework
 */
$cacheKeys = [
    VisitorImageService::CACHE_KEY,
    CompanyImageService::CACHE_KEY,
    CacheLayer::CACHE_KEY,
    RateLimiterCache::CACHE_KEY,
];
foreach ($cacheKeys as $cacheKey) {
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheKey])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheKey] = [];
    }
}
CacheLayerUtility::registerCacheLayers();

/**
 * CacheHash: Add LUX parameters to excluded variables
 */
CacheHashUtility::addLuxArgumentsToExcludedVariables();
