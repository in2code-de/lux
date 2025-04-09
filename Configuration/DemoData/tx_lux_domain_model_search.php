<?php

return [
    [
        'visitor' => 1,
        'tstamp' => (new DateTime('2 days ago'))->modify('+ 450 minutes')->getTimestamp(),
        'crdate' => (new DateTime('2 days ago'))->modify('+ 450 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'searchterm' => 'Marketing Automation Solution',
        'pagevisit' => 1,
    ],
    [
        'visitor' => 2,
        'tstamp' => (new DateTime('11 days ago'))->modify('+ 333 minutes')->getTimestamp(),
        'crdate' => (new DateTime('11 days ago'))->modify('+ 333 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'searchterm' => 'LUX TYPO3',
        'pagevisit' => 4,
    ],
    [
        'visitor' => 2,
        'tstamp' => (new DateTime('11 days ago'))->modify('+ 336 minutes')->getTimestamp(),
        'crdate' => (new DateTime('11 days ago'))->modify('+ 336 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'searchterm' => 'Newsletter E-Mail Marketing',
        'pagevisit' => 8,
    ],
];
