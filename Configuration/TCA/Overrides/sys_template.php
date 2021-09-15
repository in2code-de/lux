<?php

call_user_func(
    static function () {
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
