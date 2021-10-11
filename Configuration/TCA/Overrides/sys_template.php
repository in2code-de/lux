<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(
    static function () {
        /**
         * Add TypoScript Static Template
         */
        ExtensionManagementUtility::addStaticFile(
            'lux',
            'Configuration/TypoScript/',
            'Main TypoScript'
        );
    }
);
