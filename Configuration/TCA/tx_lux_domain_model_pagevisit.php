<?php
use In2code\Lux\Domain\Model\Pagevisit;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Pagevisit::TABLE_NAME,
        'label' => 'page',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY crdate DESC',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:lux/Resources/Public/Icons/' . Pagevisit::TABLE_NAME . '.svg',
        'rootLevel' => -1
    ],
    'interface' => [
        'showRecordFieldList' => 'page,language,crdate,referrer,domain,visitor',
    ],
    'types' => [
        '1' => ['showitem' => 'page,language,crdate,referrer,domain,visitor'],
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
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => Pagevisit::TABLE_NAME,
                'foreign_table_where' => 'AND ' . Pagevisit::TABLE_NAME . '.pid=###CURRENT_PID### AND ' .
                    Pagevisit::TABLE_NAME . '.sys_language_uid IN (-1,0)',
                'default' => 0
            ]
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ]
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ]
        ],
        'starttime' => [
            'exclude' => true,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0
            ]
        ],
        'endtime' => [
            'exclude' => true,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0
            ]
        ],

        'crdate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Pagevisit::TABLE_NAME . '.crdate',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true
            ]
        ],
        'page' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Pagevisit::TABLE_NAME . '.page',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'pages',
                'foreign_table_where' => 'ORDER BY pages.title',
                'default' => 0,
                'readOnly' => true
            ],
        ],
        'language' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Pagevisit::TABLE_NAME . '.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'default' => 0,
                'readOnly' => true
            ],
        ],
        'referrer' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Pagevisit::TABLE_NAME . '.referrer',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true,
                'default' => ''
            ]
        ],
        'domain' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Pagevisit::TABLE_NAME . '.domain',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true
            ]
        ],
        'visitor' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Pagevisit::TABLE_NAME . '.visitor',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => \In2code\Lux\Domain\Model\Visitor::TABLE_NAME,
                'default' => 0,
                'readOnly' => true
            ]
        ]
    ]
];
