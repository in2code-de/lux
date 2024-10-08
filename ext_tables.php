<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(
    function () {

        /**
         * Register Icons
         */
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconRegistry::class
        );
        $iconRegistry->registerIcon(
            'extension-lux-module',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:lux/Resources/Public/Icons/lux_white.svg']
        );
        $iconRegistry->registerIcon(
            'extension-lux',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:lux/Resources/Public/Icons/lux.svg']
        );
        $iconRegistry->registerIcon(
            'extension-lux-turquoise',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:lux/Resources/Public/Icons/Extension.svg']
        );
        $iconRegistry->registerIcon(
            'extension-lux-module-analysis',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:lux/Resources/Public/Icons/lux_module_analysis.svg']
        );
        $iconRegistry->registerIcon(
            'extension-lux-module-lead',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:lux/Resources/Public/Icons/lux_module_lead.svg']
        );
        $iconRegistry->registerIcon(
            'extension-lux-module-workflow',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:lux/Resources/Public/Icons/lux_module_workflow.svg']
        );
        $iconRegistry->registerIcon(
            'extension-lux-star',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:lux/Resources/Public/Icons/star.svg']
        );

        /**
         * Hide modules by configuration
         */
        if (\In2code\Lux\Utility\ConfigurationUtility::isAnalysisModuleDisabled()) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
                'options.hideModules := addToList(lux_LuxAnalysis)'
            );
        }
        if (\In2code\Lux\Utility\ConfigurationUtility::isLeadModuleDisabled()) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
                'options.hideModules := addToList(lux_LuxLead)'
            );
        }
        if (\In2code\Lux\Utility\ConfigurationUtility::isWorkflowModuleDisabled()) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
                'options.hideModules := addToList(lux_LuxWorkflow)'
            );
        }

        /**
         * Add backend CSS (for CKEditor 5 Email4link)
         */
        $GLOBALS['TBE_STYLES']['skins']['lux']['stylesheetDirectories']['ckeditor']
            = 'EXT:lux/Resources/Public/Css/CKEditor/';
    }
);
