<?php

use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Utm;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Utm::TABLE_NAME,
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY uid DESC',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:lux/Resources/Public/Icons/' . Utm::TABLE_NAME . '.svg',
        'rootLevel' => -1,
    ],
    'types' => [
        '1' => [
            'showitem' => 'utm_source,utm_medium,utm_campaign,utm_id,utm_term,utm_content,',
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
                'foreign_table' => Utm::TABLE_NAME,
                'foreign_table_where' => 'AND ' . Utm::TABLE_NAME . '.pid=###CURRENT_PID### AND ' .
                    Utm::TABLE_NAME . '.sys_language_uid IN (-1,0)',
                'default' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

        'pagevisit' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Utm::TABLE_NAME . '.pagevisit',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => Pagevisit::TABLE_NAME,
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'newsvisit' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Utm::TABLE_NAME . '.newsvisit',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => Newsvisit::TABLE_NAME,
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'crdate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Utm::TABLE_NAME . '.crdate',
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
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Utm::TABLE_NAME . '.tstamp',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true,
            ],
        ],
        'utm_source' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Utm::TABLE_NAME . '.utm_source',
            'config' => [
                'type' => 'text',
                'cols' => 500,
                'rows' => 8,
                'readOnly' => true,
            ],
        ],
        'utm_medium' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Utm::TABLE_NAME . '.utm_medium',
            'config' => [
                'type' => 'text',
                'cols' => 500,
                'rows' => 8,
                'readOnly' => true,
            ],
        ],
        'utm_campaign' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Utm::TABLE_NAME . '.utm_campaign',
            'config' => [
                'type' => 'text',
                'cols' => 500,
                'rows' => 8,
                'readOnly' => true,
            ],
        ],
        'utm_id' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Utm::TABLE_NAME . '.utm_id',
            'config' => [
                'type' => 'text',
                'cols' => 500,
                'rows' => 8,
                'readOnly' => true,
            ],
        ],
        'utm_term' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Utm::TABLE_NAME . '.utm_term',
            'config' => [
                'type' => 'text',
                'cols' => 500,
                'rows' => 8,
                'readOnly' => true,
            ],
        ],
        'utm_content' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Utm::TABLE_NAME . '.utm_content',
            'config' => [
                'type' => 'text',
                'cols' => 500,
                'rows' => 8,
                'readOnly' => true,
            ],
        ],
    ],
];
