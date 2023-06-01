<?php

use In2code\Lux\Domain\Model\Company;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME,
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY title ASC',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:lux/Resources/Public/Icons/' . Company::TABLE_NAME . '.svg',
        'rootLevel' => -1,
        'hideTable' => true,
    ],
    'types' => [
        '1' => [
            'showitem' => 'title,branch_code,city,contacts,continent,country_code,region,street,zip,domain,' .
                'founding_year,phone,revenue,revenue_class,size,size_class,description,category',
        ],
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
                'foreign_table' => Company::TABLE_NAME,
                'foreign_table_where' => 'AND ' . Company::TABLE_NAME . '.pid=###CURRENT_PID### AND ' .
                    Company::TABLE_NAME . '.sys_language_uid IN (-1,0)',
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
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.crdate',
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
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.tstamp',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true,
            ],
        ],

        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.title',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'branch_code' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.branch_code',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'city' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.city',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'contacts' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.contacts',
            'config' => [
                'type' => 'text',
                'cols' => 500,
                'rows' => 8,
                'readOnly' => true,
            ],
        ],
        'continent' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.continent',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'country_code' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.country_code',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'region' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.region',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'street' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.street',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'zip' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.zip',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'domain' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.domain',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'founding_year' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.founding_year',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'phone' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.phone',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'revenue' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.revenue',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'revenue_class' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.revenue_class',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'size' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.size',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'size_class' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.size_class',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'description' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.description',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'category' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Company::TABLE_NAME . '.category',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'sys_category',
                'foreign_table_where' => 'lux_company_category=1 ORDER BY title ASC',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
    ],
];
