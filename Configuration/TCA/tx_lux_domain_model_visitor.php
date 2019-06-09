<?php
use In2code\Lux\Domain\Model\Visitor;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME,
        'label' => 'uid',
        'label_userFunc' => \In2code\Lux\TCA\VisitorTitle::class . '->getContactTitle',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY tstamp DESC',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'blacklisted',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:lux/Resources/Public/Icons/' . Visitor::TABLE_NAME . '.svg',
        'searchFields' => 'email,id_cookie,referrer',
        'rootLevel' => -1
    ],
    'interface' => [
        'showRecordFieldList' =>
            'scoring,categoryscorings,identified,blacklisted,visits,email,idcookies,crdate,tstamp,attributes,' .
            'pagevisits,downloads,referrer,ip_address,ipinformations,logs,description',
    ],
    'types' => [
        '1' => [
            'showitem' =>
                '--palette--;Lead;scoring,categoryscorings,--palette--;Lead;visits,--palette--;Lead;mail,' .
                '--palette--;Lead;time,attributes,--palette--;Lead;referrer,' .
                '--div--;LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' .
                'tx_lux_domain_model_visitor.tab.enrichments,ip_address,ipinformations,' .
                '--div--;LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' .
                'tx_lux_domain_model_visitor.tab.pagevisits,pagevisits,' .
                '--div--;LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' .
                'tx_lux_domain_model_visitor.tab.downloads,downloads,' .
                '--div--;LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' .
                'tx_lux_domain_model_visitor.tab.logs,logs,' .
                '--div--;LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' .
                'tx_lux_domain_model_visitor.tab.description,description'
        ],
    ],
    'palettes' => [
        'scoring' => [
            'showitem' => 'scoring,identified'
        ],
        'visits' => [
            'showitem' => 'blacklisted,visits'
        ],
        'mail' => [
            'showitem' => 'email,idcookies'
        ],
        'time' => [
            'showitem' => 'crdate,tstamp'
        ],
        'referrer' => [
            'showitem' => 'referrer'
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
                    ['', 0],
                ],
                'foreign_table' => Visitor::TABLE_NAME,
                'foreign_table_where' => 'AND ' . Visitor::TABLE_NAME . '.pid=###CURRENT_PID### AND ' .
                    Visitor::TABLE_NAME . '.sys_language_uid IN (-1,0)',
                'default' => 0
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],

        'crdate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.crdate',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true
            ]
        ],
        'tstamp' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.tstamp',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true
            ]
        ],
        'scoring' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.scoring',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'default' => 0
            ]
        ],
        'categoryscorings' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.categoryscorings',
            'config' => [
                'type' => 'inline',
                'foreign_table' => \In2code\Lux\Domain\Model\Categoryscoring::TABLE_NAME,
                'foreign_field' => 'visitor',
                'maxitems' => 1000,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ]
            ]
        ],
        'identified' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.identified',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
                'default' => 0
            ]
        ],
        'blacklisted' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.blacklisted',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
                'default' => 0
            ]
        ],
        'visits' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.visits',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'default' => 0
            ]
        ],
        'email' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.email',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        'idcookies' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.idcookies',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => \In2code\Lux\Domain\Model\Idcookie::TABLE_NAME,
                'foreign_table_where' => 'ORDER BY ' . \In2code\Lux\Domain\Model\Idcookie::TABLE_NAME . '.uid DESC',
                'max_size' => 100,
                'minitems' => 0,
                'readOnly' => true
            ]
        ],
        'pagevisits' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.pagevisits',
            'config' => [
                'type' => 'inline',
                'foreign_table' => \In2code\Lux\Domain\Model\Pagevisit::TABLE_NAME,
                'foreign_field' => 'visitor',
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
        'attributes' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.attributes',
            'config' => [
                'type' => 'inline',
                'foreign_table' => \In2code\Lux\Domain\Model\Attribute::TABLE_NAME,
                'foreign_field' => 'visitor',
                'maxitems' => 1000,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ]
            ]
        ],
        'referrer' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.referrer',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        'ip_address' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.ip_address',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        'ipinformations' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.ipinformations',
            'config' => [
                'type' => 'inline',
                'foreign_table' => \In2code\Lux\Domain\Model\Ipinformation::TABLE_NAME,
                'foreign_field' => 'visitor',
                'maxitems' => 20,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ]
            ]
        ],
        'downloads' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.downloads',
            'config' => [
                'type' => 'inline',
                'foreign_table' => \In2code\Lux\Domain\Model\Download::TABLE_NAME,
                'foreign_field' => 'visitor',
                'maxitems' => 1000,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ]
            ]
        ],
        'logs' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.logs',
            'config' => [
                'type' => 'inline',
                'foreign_table' => \In2code\Lux\Domain\Model\Log::TABLE_NAME,
                'foreign_field' => 'visitor',
                'maxitems' => 1000,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ]
            ]
        ],
        'description' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.description',
            'config' => [
                'type' => 'text',
                'cols' => 500,
                'rows' => 8
            ]
        ]
    ]
];
