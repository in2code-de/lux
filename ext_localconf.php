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
            [
                \In2code\Lux\Controller\FrontendController::class => 'dispatchRequest'
            ],
            [
                \In2code\Lux\Controller\FrontendController::class => 'dispatchRequest'
            ]
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Lux',
            'Pi1',
            [
                \In2code\Lux\Controller\FrontendController::class => 'trackingOptOut'
            ],
            [
                \In2code\Lux\Controller\FrontendController::class => ''
            ]
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
        // Show page overview (leads or analysis) in page module
        if (\In2code\Lux\Utility\ConfigurationUtility::isPageOverviewDisabled() === false) {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][1634669927]
                = \In2code\Lux\Hooks\PageOverview::class . '->render';
        }
        // Linkhandler for Link Listener
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc'][]
            = \In2code\Lux\Hooks\LuxLinkListenerLinkhandler::class . '->postProcessTypoLink';

        /**
         * Register Slots
         */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
        );
        // Log: New visitor
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Factory\VisitorFactory::class,
            'newVisitor',
            \In2code\Lux\Slot\Log::class,
            'logNewVisitor',
            false
        );
        // Log: Identified visitor by listening to field inputs
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Tracker\AttributeTracker::class,
            'isIdentifiedByFieldlistening',
            \In2code\Lux\Slot\Log::class,
            'logIdentifiedVisitor',
            false
        );
        // Log: Identified visitor by listening to form submits
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Tracker\AttributeTracker::class,
            'isIdentifiedByFormlistening',
            \In2code\Lux\Slot\Log::class,
            'logIdentifiedVisitorByFormListening',
            false
        );
        // Log: Identified visitor by email4link
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Tracker\AttributeTracker::class,
            'isIdentifiedByEmail4link',
            \In2code\Lux\Slot\Log::class,
            'logIdentifiedVisitorByEmail4Link',
            false
        );
        // Log: Identified visitor by luxletterlink
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Tracker\AttributeTracker::class,
            'isIdentifiedByLuxletterlink',
            \In2code\Lux\Slot\Log::class,
            'logIdentifiedVisitorByLuxletterlink',
            false
        );
        // Log: Identified visitor by frontenduser authentication
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Tracker\AttributeTracker::class,
            'isIdentifiedByFrontendauthentication',
            \In2code\Lux\Slot\Log::class,
            'logIdentifiedVisitorByFrontendauthentication',
            false
        );
        // Log: email4link send mail
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Service\Email\SendAssetEmail4LinkService::class,
            'email4linkSendEmail',
            \In2code\Lux\Slot\Log::class,
            'logEmail4LinkEmail',
            false
        );
        // Log: email4link send mail
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Service\Email\SendAssetEmail4LinkService::class,
            'email4linkSendEmailFailed',
            \In2code\Lux\Slot\Log::class,
            'logEmail4LinkEmailFailed',
            false
        );
        // Log: download tracking
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Tracker\DownloadTracker::class,
            'addDownload',
            \In2code\Lux\Slot\Log::class,
            'logDownload',
            false
        );
        // Log: search tracking
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Tracker\SearchTracker::class,
            'track',
            \In2code\Lux\Slot\Log::class,
            'logSearch',
            false
        );
        // Log: linklistener click tracking
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Tracker\LinkClickTracker::class,
            'addLinkClick',
            \In2code\Lux\Slot\Log::class,
            'logLinkClick',
            false
        );
        // Calculate main scoring
        $signalSlotDispatcher->connect(
            \In2code\Lux\Controller\FrontendController::class,
            'afterTracking',
            \In2code\Lux\Domain\Service\ScoringService::class,
            'calculateAndSetScoring',
            false
        );
        // Calculate category scoring
        $signalSlotDispatcher->connect(
            \In2code\Lux\Controller\FrontendController::class,
            'afterTracking',
            \In2code\Lux\Domain\Service\CategoryScoringService::class,
            'calculateAndSetScoring',
            false
        );
        // Add finisher
        $signalSlotDispatcher->connect(
            \In2code\Lux\Controller\FrontendController::class,
            'afterTracking',
            \In2code\Lux\Domain\Finisher\FinisherHandler::class,
            'startFinisher',
            false
        );
        // Add a class to stop tracking
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Factory\VisitorFactory::class,
            'stopAnyProcessBeforePersistence',
            \In2code\Lux\Domain\Tracker\StopTracking::class,
            'stop',
            false
        );

        /**
         * CK editor configuration
         */
        if (\In2code\Lux\Utility\ConfigurationUtility::isCkEditorConfigurationNeeded()) {
            $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['lux'] = 'EXT:lux/Configuration/Yaml/CkEditor.yaml';

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
            \In2code\Lux\Domain\Service\VisitorImageService::CACHE_KEY,
            \In2code\Lux\Domain\Cache\CacheLayer::CACHE_KEY
        ];
        foreach ($cacheKeys as $cacheKey) {
            if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheKey])) {
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheKey] = [];
            }
        }
        \In2code\Lux\Utility\CacheLayerUtility::registerCacheLayers();
    }
);
