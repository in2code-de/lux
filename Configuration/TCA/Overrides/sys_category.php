<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$columns = [
    'lux_category' => [
        'exclude' => true,
        'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:sys_category.lux_category',
        'config' => [
            'type' => 'check'
        ]
    ]
];
ExtensionManagementUtility::addTCAcolumns('sys_category', $columns);
ExtensionManagementUtility::addToAllTCAtypes('sys_category', 'lux_category', '', 'after:parent');
