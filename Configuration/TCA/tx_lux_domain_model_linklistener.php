<?php
use In2code\Lux\Domain\Model\Linklistener;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linklistener::TABLE_NAME,
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY title ASC',
        'delete' => 'deleted',
        'iconfile' => 'EXT:lux/Resources/Public/Icons/' . Linklistener::TABLE_NAME . '.svg',
        'rootLevel' => -1
    ],
    'interface' => [
        'showRecordFieldList' => 'title,link,category,linkclicks',
    ],
    'types' => [
        '1' => ['showitem' => 'title,--palette--;Link;palette_link'],
    ],
    'palettes' => [
        'palette_link' => [
            'showitem' => 'link,category'
        ]
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
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0]
                ],
                'foreign_table' => Linklistener::TABLE_NAME,
                'foreign_table_where' => 'AND ' . Linklistener::TABLE_NAME . '.pid=###CURRENT_PID### AND ' .
                    Linklistener::TABLE_NAME . '.sys_language_uid IN (-1,0)',
                'default' => 0
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

        'crdate' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linklistener::TABLE_NAME . '.crdate',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true
            ]
        ],
        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linklistener::TABLE_NAME . '.title',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,required'
            ]
        ],
        'link' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linklistener::TABLE_NAME . '.link',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputLink',
                'eval' => 'required'
            ]
        ],
        'category' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linklistener::TABLE_NAME . '.category',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:pleaseChoose', ''],
                ],
                'foreign_table' => 'sys_category',
                'foreign_table_where' => 'lux_category=1 ORDER BY title ASC',
                'minitems' => 1,
                'eval' => 'int,required'
            ]
        ],
        'linkclicks' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linklistener::TABLE_NAME . '.linkclicks',
            'config' => [
                'type' => 'inline',
                'foreign_table' => \In2code\Lux\Domain\Model\Linkclick::TABLE_NAME,
                'foreign_field' => 'linklistener',
                'maxitems' => 100000,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ]
            ]
        ],
    ]
];
