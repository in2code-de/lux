<?php
use In2code\Lux\Domain\Model\Idcookie;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Idcookie::TABLE_NAME,
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
        'iconfile' => 'EXT:lux/Resources/Public/Icons/' . Idcookie::TABLE_NAME . '.svg',
        'rootLevel' => -1
    ],
    'interface' => [
        'showRecordFieldList' => 'value,domain,user_agent',
    ],
    'types' => [
        '1' => ['showitem' => 'value,domain,user_agent'],
    ],
    'columns' => [
        'value' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Idcookie::TABLE_NAME . '.value',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        'domain' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Idcookie::TABLE_NAME . '.domain',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        'user_agent' => [
            'exclude' => true,
            'label' =>
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:' . Idcookie::TABLE_NAME . '.user_agent',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ]
    ]
];
