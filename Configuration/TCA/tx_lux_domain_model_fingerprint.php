<?php
use In2code\Lux\Domain\Model\Fingerprint;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Fingerprint::TABLE_NAME,
        'label' => 'value',
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
        'iconfile' => 'EXT:lux/Resources/Public/Icons/' . Fingerprint::TABLE_NAME . '.svg',
        'rootLevel' => -1
    ],
    'interface' => [
        'showRecordFieldList' => 'value,domain,user_agent,type',
    ],
    'types' => [
        '1' => ['showitem' => 'value,domain,user_agent,type'],
    ],
    'columns' => [
        'value' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Fingerprint::TABLE_NAME . '.value',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        'domain' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Fingerprint::TABLE_NAME . '.domain',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        'user_agent' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Fingerprint::TABLE_NAME . '.user_agent',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        'type' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Fingerprint::TABLE_NAME . '.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 0,
                'items' => [
                    ['Fingerprint', 0],
                    ['Cookie', 1]
                ]
            ]
        ],
    ]
];
