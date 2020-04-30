<?php
use In2code\Lux\Domain\Model\Linkclick;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linkclick::TABLE_NAME,
        'label' => 'tag',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY crdate DESC',
        'delete' => 'deleted',
        'iconfile' => 'EXT:lux/Resources/Public/Icons/' . Linkclick::TABLE_NAME . '.svg',
        'rootLevel' => -1
    ],
    'interface' => [
        'showRecordFieldList' => 'crdate,tag,page,visitor',
    ],
    'types' => [
        '1' => ['showitem' => 'crdate,tag,page,visitor'],
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
                'foreign_table' => Linkclick::TABLE_NAME,
                'foreign_table_where' => 'AND ' . Linkclick::TABLE_NAME . '.pid=###CURRENT_PID### AND ' .
                    Linkclick::TABLE_NAME . '.sys_language_uid IN (-1,0)',
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
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linkclick::TABLE_NAME . '.crdate',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true
            ]
        ],
        'tag' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linkclick::TABLE_NAME . '.tag',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        'page' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linkclick::TABLE_NAME . '.page',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'pages',
                'foreign_table_where' => 'ORDER BY pages.title',
                'default' => 0,
                'readOnly' => true
            ],
        ],
        'visitor' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linkclick::TABLE_NAME . '.visitor',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => \In2code\Lux\Domain\Model\Visitor::TABLE_NAME,
                'size' => 1,
                'maxitems' => 1,
                'multiple' => 0,
                'default' => 0,
                'readOnly' => true
            ]
        ]
    ]
];
