<?php
if (!defined('TYPO3_MODE')) {
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

        /**
         * Include Modules
         */
        // Add Main module "LUX".
        // Acces to a main module is implicit, as soon as a user has access to at least one of its submodules.
        if (\In2code\Lux\Utility\ConfigurationUtility::isAnalysisModuleDisabled() === false
            || \In2code\Lux\Utility\ConfigurationUtility::isWorkflowModuleDisabled() === false) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
                'lux',
                '',
                '',
                null,
                [
                    'name' => 'lux',
                    'labels' => 'LLL:EXT:lux/Resources/Private/Language/locallang_mod.xlf',
                    'iconIdentifier' => 'extension-lux-module'
                ]
            );
        }
        // Add module for analysis
        if (\In2code\Lux\Utility\ConfigurationUtility::isAnalysisModuleDisabled() === false) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'In2code.lux',
                'lux',
                'analysis',
                '',
                [
                    'Analysis' => 'dashboard,content,detailPage,detailDownload',
                    'Lead' => 'list,detail,downloadCsv,remove,deactivate'
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:lux/Resources/Public/Icons/lux_module_analysis.svg',
                    'labels' => 'LLL:EXT:lux/Resources/Private/Language/locallang_mod_analysis.xlf',
                ]
            );
        }
        // Add module for leads
        if (\In2code\Lux\Utility\ConfigurationUtility::isLeadModuleDisabled() === false) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'In2code.lux',
                'lux',
                'leads',
                '',
                [
                    'Lead' => 'list,detail,downloadCsv,remove,deactivate',
                    'Analysis' => 'dashboard,content,detailPage,detailDownload',
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:lux/Resources/Public/Icons/lux_module_lead.svg',
                    'labels' => 'LLL:EXT:lux/Resources/Private/Language/locallang_mod_lead.xlf',
                ]
            );
        }
        // Add module for workflow
        if (\In2code\Lux\Utility\ConfigurationUtility::isWorkflowModuleDisabled() === false) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'In2code.lux',
                'lux',
                'workflow',
                '',
                [
                    'Workflow' => 'list,new,create,edit,update,delete'
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:lux/Resources/Public/Icons/lux_module_workflow.svg',
                    'labels' => 'LLL:EXT:lux/Resources/Private/Language/locallang_mod_workflow.xlf',
                ]
            );
        }

        /**
         * Add TypoScript Static Template
         */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            'lux',
            'Configuration/TypoScript/',
            'Main TypoScript'
        );
    }
);
