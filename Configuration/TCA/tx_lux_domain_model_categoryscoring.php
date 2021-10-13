<?php

use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Visitor;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Categoryscoring::TABLE_NAME,
        'label' => 'category',
        'label_alt' => 'scoring',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY crdate DESC',
        'delete' => 'deleted',
        'enablecolumns' => [],
        'iconfile' => 'EXT:lux/Resources/Public/Icons/' . Categoryscoring::TABLE_NAME . '.svg',
        'rootLevel' => -1
    ],
    'types' => [
        '1' => ['showitem' => 'scoring,category,visitor'],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'default' => 0,
                'items' => [
                    ['LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1],
                    ['LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0]
                ]
            ]
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => Categoryscoring::TABLE_NAME,
                'foreign_table_where' => 'AND ' . Categoryscoring::TABLE_NAME . '.pid=###CURRENT_PID### AND ' .
                    Categoryscoring::TABLE_NAME . '.sys_language_uid IN (-1,0)',
                'default' => 0
            ]
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ]
        ],

        'scoring' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Categoryscoring::TABLE_NAME . '.scoring',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'default' => 0
            ]
        ],
        'category' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Categoryscoring::TABLE_NAME . '.category',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'sys_category',
                'foreign_table_where' => 'lux_category=1 ORDER BY title ASC',
                'default' => 0,
                'readOnly' => true
            ],
        ],
        'visitor' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Categoryscoring::TABLE_NAME . '.visitor',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => Visitor::TABLE_NAME,
                'default' => 0,
                'readOnly' => true
            ]
        ]
    ]
];
