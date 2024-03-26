<?php

use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\TCA\GetStatusForLogSelection;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Log::TABLE_NAME,
        'label' => 'status',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY crdate DESC',
        'delete' => 'deleted',
        'iconfile' => 'EXT:lux/Resources/Public/Icons/' . Log::TABLE_NAME . '.svg',
        'rootLevel' => -1,
        'hideTable' => true,
    ],
    'types' => [
        '1' => ['showitem' => 'crdate,status,properties,site,visitor'],
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
                'foreign_table' => Log::TABLE_NAME,
                'foreign_table_where' => 'AND ' . Log::TABLE_NAME . '.pid=###CURRENT_PID### AND ' .
                    Log::TABLE_NAME . '.sys_language_uid IN (-1,0)',
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
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Log::TABLE_NAME . '.crdate',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true,
            ],
        ],
        'status' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Log::TABLE_NAME . '.status',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'size' => 1,
                'maxitems' => 1,
                'readOnly' => true,
                'default' => 0,
                'itemsProcFunc' => GetStatusForLogSelection::class . '->addOptions',
            ],
        ],
        'properties' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Log::TABLE_NAME . '.properties',
            'config' => [
                'type' => 'text',
                'cols' => 32,
                'rows' => 5,
                'readOnly' => true,
            ],
        ],
        'site' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Log::TABLE_NAME . '.site',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true,
            ],
        ],
        'visitor' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Log::TABLE_NAME . '.visitor',
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
