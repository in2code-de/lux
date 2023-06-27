<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$columns = [
    'lux_category' => [
        'exclude' => true,
        'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:sys_category.lux_category',
        'description' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:sys_category.lux_category.description',
        'config' => [
            'type' => 'check',
            'default' => 0,
        ],
    ],
    'lux_company_category' => [
        'exclude' => true,
        'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:sys_category.lux_company_category',
        'description' =>
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:sys_category.lux_company_category.description',
        'config' => [
            'type' => 'check',
            'default' => 0,
        ],
    ],
];
ExtensionManagementUtility::addTCAcolumns('sys_category', $columns);
ExtensionManagementUtility::addToAllTCAtypes('sys_category', 'lux_category,lux_company_category', '', 'after:parent');
