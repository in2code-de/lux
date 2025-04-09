<?php

use In2code\Lux\Domain\Service\DemoDataService;

return [
    [
        'tstamp' => (new DateTime('10 hours ago'))->modify('+ 77 minutes')->getTimestamp(),
        'crdate' => (new DateTime('10 hours ago'))->modify('+ 77 minutes')->getTimestamp(),
        'visitor' => 1,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'sys_language_uid' => -1,
        'linklistener' => 1,
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'tstamp' => (new DateTime('3 days ago'))->modify('+ 54 minutes')->getTimestamp(),
        'crdate' => (new DateTime('3 days ago'))->modify('+ 54 minutes')->getTimestamp(),
        'visitor' => 1,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'sys_language_uid' => -1,
        'linklistener' => 2,
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'tstamp' => (new DateTime('1234 hours ago'))->getTimestamp(),
        'crdate' => (new DateTime('1234 hours ago'))->getTimestamp(),
        'visitor' => 2,
        'page' => 1,
        'sys_language_uid' => -1,
        'linklistener' => 3,
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'tstamp' => (new DateTime('2345 hours ago'))->getTimestamp(),
        'crdate' => (new DateTime('2345 hours ago'))->getTimestamp(),
        'visitor' => 3,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'sys_language_uid' => -1,
        'linklistener' => 1,
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'tstamp' => (new DateTime('2345 hours ago'))->getTimestamp(),
        'crdate' => (new DateTime('2345 hours ago'))->getTimestamp(),
        'visitor' => 3,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'sys_language_uid' => -1,
        'linklistener' => 2,
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'tstamp' => (new DateTime('2345 hours ago'))->getTimestamp(),
        'crdate' => (new DateTime('2345 hours ago'))->getTimestamp(),
        'visitor' => 3,
        'page' => DemoDataService::getFirstRootPageIdentifier(),
        'sys_language_uid' => -1,
        'linklistener' => 3,
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'tstamp' => (new DateTime('2345 hours ago'))->getTimestamp(),
        'crdate' => (new DateTime('2345 hours ago'))->getTimestamp(),
        'visitor' => 4,
        'page' => DemoDataService::getFirstRootPageIdentifier(),
        'sys_language_uid' => -1,
        'linklistener' => 3,
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
];
