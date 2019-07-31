<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "lux".
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'lux - TYPO3 Marketing Automation',
    'description' => 'Living User Experience - LUX - the Marketing Automation tool for TYPO3.
        Turn your visitors to leads. Identification and profiling of your visitors within your TYPO3 website.',
    'category' => 'plugin',
    'version' => '5.0.0',
    'author' => 'Alex Kellner',
    'author_email' => 'alexander.kellner@in2code.de',
    'author_company' => 'in2code.de',
    'state' => 'stable',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.5.99',
            'php' => '7.0.0-7.99.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    '_md5_values_when_last_written' => '',
];
