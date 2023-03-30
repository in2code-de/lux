<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'LUX - TYPO3 Marketing Automation',
    'description' => 'Living User Experience - LUX - the Marketing Automation tool for TYPO3.
        Turn your visitors to leads. Identification and profiling of your visitors within your TYPO3 website.',
    'category' => 'plugin',
    'version' => '28.1.0',
    'author' => 'Alex Kellner',
    'author_email' => 'alexander.kellner@in2code.de',
    'author_company' => 'in2code.de',
    'state' => 'stable',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'dashboard' => '0.0.0-0.0.0',
        ],
    ],
];
