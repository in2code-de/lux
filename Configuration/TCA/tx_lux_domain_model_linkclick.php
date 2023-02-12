<?php

use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\Visitor;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linkclick::TABLE_NAME,
        'label' => 'linklistener',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY linklistener ASC',
        'delete' => 'deleted',
        'iconfile' => 'EXT:lux/Resources/Public/Icons/' . Linkclick::TABLE_NAME . '.svg',
        'rootLevel' => -1,
        'hideTable' => true,
    ],
    'types' => [
        '1' => ['showitem' => 'crdate,linklistener,page,visitor'],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => ['type' => 'language'],
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
                'foreign_table' => Linkclick::TABLE_NAME,
                'foreign_table_where' => 'AND ' . Linkclick::TABLE_NAME . '.pid=###CURRENT_PID### AND ' .
                    Linkclick::TABLE_NAME . '.sys_language_uid IN (-1,0)',
                'default' => 0,
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
                'renderType' => 'inputDateTime',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true,
            ],
        ],
        'linklistener' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linkclick::TABLE_NAME . '.linklistener',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => Linklistener::TABLE_NAME,
                'foreign_table_where' => 'ORDER BY ' . Linklistener::TABLE_NAME . '.title',
                'default' => 0,
                'readOnly' => true,
            ],
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
                'readOnly' => true,
            ],
        ],
        'visitor' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Linkclick::TABLE_NAME . '.visitor',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => Visitor::TABLE_NAME,
                'default' => 0,
                'readOnly' => true,
            ],
        ],
    ],
];
