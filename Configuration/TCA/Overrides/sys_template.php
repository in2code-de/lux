<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

/**
 * Add TypoScript Static Template
 */
ExtensionManagementUtility::addStaticFile(
    'lux',
    'Configuration/TypoScript/',
    'Main TypoScript'
);
