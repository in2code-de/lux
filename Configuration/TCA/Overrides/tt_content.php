<?php

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
