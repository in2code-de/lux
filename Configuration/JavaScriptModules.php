<?php

return [
    'dependencies' => [
        'core',
    ],
    'imports' => [
        '@in2code/lux/' => [
            'path' => 'EXT:lux/Resources/Public/JavaScript/Lux/',
            'exclude' => [
                'EXT:backend/Resources/Public/JavaScript/modern/',
            ],
        ],
    ],
];
