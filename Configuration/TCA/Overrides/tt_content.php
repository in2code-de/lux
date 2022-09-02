<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die();

/**
 * Register Plugins
 */
ExtensionUtility::registerPlugin('lux', 'Pi1', 'Lux: TrackingOptOut');

/**
 * Disable not needed fields in tt_content
 */
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['lux_pi1'] = 'select_key,pages,recursive';

/**
 * Include Flexform
 */
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['lux_pi1'] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue(
    'lux_pi1',
    'FILE:EXT:lux/Configuration/FlexForms/FlexFormPi1.xml'
);
