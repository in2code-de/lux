<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'LUX - TYPO3 Marketing Automation',
    'description' => 'Living User Experience - LUX - the Marketing Automation tool for TYPO3.
        Turn your visitors to leads. Identification and profiling of your visitors within your TYPO3 website.',
    'category' => 'plugin',
    'version' => '22.6.0',
    'author' => 'Alex Kellner',
    'author_email' => 'alexander.kellner@in2code.de',
    'author_company' => 'in2code.de',
    'state' => 'stable',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
            'php' => '7.2.0-8.0.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ]
];
