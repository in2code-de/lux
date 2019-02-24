<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function () {

        /**
         * Include Frontend Plugins
         */
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'In2code.lux',
            'Fe',
            [
                'Frontend' => 'dispatchRequest,pageRequest,fieldListeningRequest,email4LinkRequest,downloadRequest'
            ],
            [
                'Frontend' => 'dispatchRequest,pageRequest,fieldListeningRequest,email4LinkRequest,downloadRequest'
            ]
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'In2code.lux',
            'Pi1',
            [
                'Frontend' => 'trackingOptOut'
            ],
            [
                'Frontend' => ''
            ]
        );

        /**
         * Add page TSConfig
         */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:lux/Configuration/TSConfig/Lux.typoscript">'
        );

        /**
         * Hooks
         */
        if (\In2code\Lux\Utility\ConfigurationUtility::isLastLeadsBoxInPageDisabled() === false) {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][]
                = \In2code\Lux\Hooks\PageLayoutHeader::class . '->render';
        }

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
        // Log: Identified visitor by email4link
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Tracker\AttributeTracker::class,
            'isIdentifiedByEmail4link',
            \In2code\Lux\Slot\Log::class,
            'logIdentifiedVisitorByEmail4Link',
            false
        );
        // Log: email4link send mail
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Service\SendAssetEmail4LinkService::class,
            'email4linkSendEmail',
            \In2code\Lux\Slot\Log::class,
            'logEmail4LinkEmail',
            false
        );
        // Log: email4link send mail
        $signalSlotDispatcher->connect(
            \In2code\Lux\Domain\Service\SendAssetEmail4LinkService::class,
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

        /**
         * CK editor configuration
         */
        $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['lux'] = 'EXT:lux/Configuration/Yaml/CkEditor.yaml';

        /**
         * Fluid Namespace
         */
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['lux'][] = 'In2code\Lux\ViewHelpers';

        /**
         * CommandControllers
         */
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
            \In2code\Lux\Command\LuxAnonymizeCommandController::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
            \In2code\Lux\Command\LuxCleanupCommandController::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
            \In2code\Lux\Command\LuxServiceCommandController::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
            \In2code\Lux\Command\LuxLeadCommandController::class;
    }
);
