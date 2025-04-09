<?php

use In2code\Lux\Domain\Service\DemoDataService;

return [
    [
        'visitor' => 1,
        'tstamp' => (new DateTime('2 days ago'))->modify('+ 450 minutes')->getTimestamp(),
        'crdate' => (new DateTime('2 days ago'))->modify('+ 450 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'news' => DemoDataService::getRandomNewsIdentifier(),
        'pagevisit' => 1,
    ],
    [
        'visitor' => 1,
        'tstamp' => (new DateTime('3 days ago'))->modify('+ 333 minutes')->getTimestamp(),
        'crdate' => (new DateTime('3 days ago'))->modify('+ 333 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'news' => DemoDataService::getRandomNewsIdentifier(),
        'pagevisit' => 2,
    ],
    [
        'visitor' => 2,
        'tstamp' => (new DateTime('11 days ago'))->modify('+ 336 minutes')->getTimestamp(),
        'crdate' => (new DateTime('11 days ago'))->modify('+ 336 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'news' => DemoDataService::getRandomNewsIdentifier(),
        'pagevisit' => 3,
    ],
];
