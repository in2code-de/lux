<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die();

/**
 * Register Plugins
 */
ExtensionUtility::registerPlugin(
    'Lux',
    'Pi1',
    'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:pi1.title',
    'extension-lux',
    'plugins',
    'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:pi1.description',
    'FILE:EXT:lux/Configuration/FlexForms/FlexFormPi1.xml'
);

// Todo: Remove once TYPO3 13 support is dropped
if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() === 13) {
    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:lux/Configuration/FlexForms/FlexFormPi1.xml',
        'lux_pi1'
    );
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        'pi_flexform',
        'lux_pi1',
        'after:subheader'
    );
}
