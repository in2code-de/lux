<?php

use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Ipinformation;
use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\TCA\VisitorTitle;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME,
        'label' => 'uid',
        'label_userFunc' => VisitorTitle::class . '->getContactTitle',
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
        'searchFields' => 'email,id_cookie',
        'rootLevel' => -1,
    ],
    'types' => [
        '1' => [
            'showitem' =>
                '--palette--;Lead;scoring,categoryscorings,--palette--;Lead;visits,--palette--;Lead;mail,' .
                '--palette--;Lead;time,frontenduser,attributes,' .
                '--div--;LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' .
                'tx_lux_domain_model_visitor.tab.enrichments,ip_address,ipinformations,' .
                '--div--;LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' .
                'tx_lux_domain_model_visitor.tab.pagevisits,pagevisits,newsvisits,linkclicks' .
                '--div--;LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' .
                'tx_lux_domain_model_visitor.tab.downloads,downloads,' .
                '--div--;LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' .
                'tx_lux_domain_model_visitor.tab.logs,logs,' .
                '--div--;LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' .
                'tx_lux_domain_model_visitor.tab.description,description',
        ],
    ],
    'palettes' => [
        'scoring' => [
            'showitem' => 'scoring,identified',
        ],
        'visits' => [
            'showitem' => 'blacklisted,visits',
        ],
        'mail' => [
            'showitem' => 'email,company,--linebreak--,fingerprints',
        ],
        'time' => [
            'showitem' => 'crdate,tstamp',
        ],
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
                    ['LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0],
                ],
            ],
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
                'foreign_table' => Visitor::TABLE_NAME,
                'foreign_table_where' => 'AND ' . Visitor::TABLE_NAME . '.pid=###CURRENT_PID### AND ' .
                    Visitor::TABLE_NAME . '.sys_language_uid IN (-1,0)',
                'default' => 0,
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
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
            ],
        ],

        'crdate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.crdate',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true,
            ],
        ],
        'tstamp' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.tstamp',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true,
            ],
        ],
        'scoring' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.scoring',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'default' => 0,
            ],
        ],
        'categoryscorings' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.categoryscorings',
            'config' => [
                'type' => 'inline',
                'foreign_table' => Categoryscoring::TABLE_NAME,
                'foreign_field' => 'visitor',
                'maxitems' => 1000,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                ],
            ],
        ],
        'identified' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.identified',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
                'default' => 0,
            ],
        ],
        'blacklisted' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.blacklisted',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
                'default' => 0,
            ],
        ],
        'visits' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.visits',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'default' => 0,
            ],
        ],
        'email' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.email',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'company' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.company',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'fingerprints' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.fingerprints',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => Fingerprint::TABLE_NAME,
                'foreign_table_where' => 'ORDER BY ' . Fingerprint::TABLE_NAME . '.uid DESC',
                'max_size' => 100,
                'minitems' => 0,
                'readOnly' => true,
            ],
        ],
        'pagevisits' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.pagevisits',
            'config' => [
                'type' => 'inline',
                'foreign_table' => Pagevisit::TABLE_NAME,
                'foreign_field' => 'visitor',
                'maxitems' => 100000,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                ],
            ],
        ],
        'newsvisits' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.newsvisits',
            'config' => [
                'type' => 'inline',
                'foreign_table' => Newsvisit::TABLE_NAME,
                'foreign_field' => 'visitor',
                'maxitems' => 100000,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                ],
            ],
        ],
        'linkclicks' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.linkclicks',
            'config' => [
                'type' => 'inline',
                'foreign_table' => Linkclick::TABLE_NAME,
                'foreign_field' => 'visitor',
                'maxitems' => 100000,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                ],
            ],
        ],
        'attributes' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.attributes',
            'config' => [
                'type' => 'inline',
                'foreign_table' => Attribute::TABLE_NAME,
                'foreign_field' => 'visitor',
                'maxitems' => 1000,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                ],
            ],
        ],
        'ip_address' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.ip_address',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'ipinformations' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.ipinformations',
            'config' => [
                'type' => 'inline',
                'foreign_table' => Ipinformation::TABLE_NAME,
                'foreign_field' => 'visitor',
                'maxitems' => 20,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                ],
            ],
        ],
        'downloads' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.downloads',
            'config' => [
                'type' => 'inline',
                'foreign_table' => Download::TABLE_NAME,
                'foreign_field' => 'visitor',
                'maxitems' => 1000,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                ],
            ],
        ],
        'logs' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.logs',
            'config' => [
                'type' => 'inline',
                'foreign_table' => Log::TABLE_NAME,
                'foreign_field' => 'visitor',
                'maxitems' => 1000,
                'appearance' => [
                    'collapse' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                ],
            ],
        ],
        'description' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Visitor::TABLE_NAME . '.description',
            'config' => [
                'type' => 'text',
                'cols' => 500,
                'rows' => 8,
            ],
        ],
        'frontenduser' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:'
                . Visitor::TABLE_NAME . '.frontenduser',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'fe_users',
                'foreign_table_where' => 'AND fe_users.deleted = 0',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
    ],
];
