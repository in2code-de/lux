<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(
    function () {

        /**
         * Include Frontend Plugins
         */
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Lux',
            'Fe',
            [\In2code\Lux\Controller\FrontendController::class => 'dispatchRequest']
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Lux',
            'Email4link',
            [\In2code\Lux\Controller\FrontendController::class => 'email4link']
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Lux',
            'Pi1',
            [\In2code\Lux\Controller\FrontendController::class => 'trackingOptOut']
        );

        /**
         * Add page TSConfig
         */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            '@import \'EXT:lux/Configuration/TSConfig/Lux.typoscript\''
        );

        /**
         * Hooks
         */
        // Linkhandler for Link Listener
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc'][]
            = \In2code\Lux\Hooks\LuxLinkListenerLinkhandler::class . '->postProcessTypoLink';

        /**
         * CK editor configuration
         */
        if (\In2code\Lux\Utility\ConfigurationUtility::isCkEditorConfigurationNeeded()) {
            $ckConfiguration = 'EXT:lux/Configuration/Yaml/CkEditor.yaml';
            $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['lux'] = $ckConfiguration;

            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
                'RTE.default.preset = lux'
            );
        }

        /**
         * Fluid Namespace
         */
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['lux'][] = 'In2code\Lux\ViewHelpers';

        /**
         * Caching framework
         */
        $cacheKeys = [
            \In2code\Lux\Domain\Service\Image\VisitorImageService::CACHE_KEY,
            \In2code\Lux\Domain\Service\Image\CompanyImageService::CACHE_KEY,
            \In2code\Lux\Domain\Cache\CacheLayer::CACHE_KEY
        ];
        foreach ($cacheKeys as $cacheKey) {
            if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheKey])) {
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheKey] = [];
            }
        }
        \In2code\Lux\Utility\CacheLayerUtility::registerCacheLayers();

        /**
         * CacheHash: Add LUX parameters to excluded variables
         */
        \In2code\Lux\Utility\CacheHashUtility::addLuxArgumentsToExcludedVariables();
    }
);
