<?php

use In2code\Lux\Domain\Service\DemoDataService;

return [
    [
        'uid' => 1,
        'visitor' => 1,
        'page' => DemoDataService::getFirstRootPageIdentifier(),
        'tstamp' => (new DateTime('31 days ago'))->getTimestamp(),
        'crdate' => (new DateTime('31 days ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://www.google.com/',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 2,
        'visitor' => 1,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('31 days ago'))->modify('+ 1 minute')->getTimestamp(),
        'crdate' => (new DateTime('31 days ago'))->modify('+ 1 minute')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 3,
        'visitor' => 1,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('31 days ago'))->modify('+ 3 minute')->getTimestamp(),
        'crdate' => (new DateTime('31 days ago'))->modify('+ 3 minute')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 4,
        'visitor' => 1,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('22 days ago'))->getTimestamp(),
        'crdate' => (new DateTime('22 days ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://extensions.typo3.org/',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 5,
        'visitor' => 1,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('12 hours ago'))->getTimestamp(),
        'crdate' => (new DateTime('12 hours ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://chatgpt.com/',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 6,
        'visitor' => 1,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('11 hours ago'))->getTimestamp(),
        'crdate' => (new DateTime('11 hours ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 7,
        'visitor' => 1,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('9 hours ago'))->getTimestamp(),
        'crdate' => (new DateTime('9 hours ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 8,
        'visitor' => 1,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('5 hours ago'))->getTimestamp(),
        'crdate' => (new DateTime('5 hours ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 9,
        'visitor' => 1,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('2 hours ago'))->modify('+ 200 minutes')->getTimestamp(),
        'crdate' => (new DateTime('2 hours ago'))->modify('+ 200 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 10,
        'visitor' => 1,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('2 hours ago'))->modify('+ 220 minutes')->getTimestamp(),
        'crdate' => (new DateTime('2 hours ago'))->modify('+ 220 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 11,
        'visitor' => 1,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('2 hours ago'))->modify('+ 222 minutes')->getTimestamp(),
        'crdate' => (new DateTime('2 hours ago'))->modify('+ 222 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 12,
        'visitor' => 2,
        'page' => DemoDataService::getFirstRootPageIdentifier(),
        'tstamp' => (new DateTime('47 days ago'))->modify('+ 555 minutes')->getTimestamp(),
        'crdate' => (new DateTime('47 days ago'))->modify('+ 555 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://www.typo3.org/',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 13,
        'visitor' => 2,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('7 days ago'))->modify('+ 555 minutes')->getTimestamp(),
        'crdate' => (new DateTime('7 days ago'))->modify('+ 555 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://gemini.google.com/',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 14,
        'visitor' => 2,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('7 days ago'))->modify('+ 557 minutes')->getTimestamp(),
        'crdate' => (new DateTime('7 days ago'))->modify('+ 557 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'landingpage.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 15,
        'visitor' => 2,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('7 days ago'))->modify('+ 559 minutes')->getTimestamp(),
        'crdate' => (new DateTime('7 days ago'))->modify('+ 559 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 16,
        'visitor' => 2,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('6 days ago'))->modify('+ 70 minutes')->getTimestamp(),
        'crdate' => (new DateTime('6 days ago'))->modify('+ 70 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 17,
        'visitor' => 2,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('3 days ago'))->modify('+ 700 minutes')->getTimestamp(),
        'crdate' => (new DateTime('3 days ago'))->modify('+ 700 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 18,
        'visitor' => 2,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'tstamp' => (new DateTime('2 days ago'))->modify('+ 123 minutes')->getTimestamp(),
        'crdate' => (new DateTime('2 days ago'))->modify('+ 123 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'landingpage.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 19,
        'visitor' => 3,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('55 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('55 days ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://www.linkedin.com/',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 20,
        'visitor' => 3,
        'page' => DemoDataService::getFirstRootPageIdentifier(),
        'crdate' => (new DateTime('55 days ago'))->modify('+ 2 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('55 days ago'))->modify('+ 2 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 21,
        'visitor' => 3,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('3 days ago'))->modify('+ 222 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('3 days ago'))->modify('+ 222 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://www.facebook.com/',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 22,
        'visitor' => 4,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('130 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('130 days ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://www.linkedin.com/',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 23,
        'visitor' => 4,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('1 days ago'))->modify('+ 543 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('1 days ago'))->modify('+ 543 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://chatgpt.com/',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 24,
        'visitor' => 5,
        'page' => DemoDataService::getFirstRootPageIdentifier(),
        'crdate' => (new DateTime('55 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('55 days ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://www.ebay.com/',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 25,
        'visitor' => 6,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('188 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('188 days ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://www.facebook.com/in2code.de',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 26,
        'visitor' => 6,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('6 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('6 days ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://www.typo3.org/',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 27,
        'visitor' => 6,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('6 days ago'))->modify('+ 5 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('6 days ago'))->modify('+ 5 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 28,
        'visitor' => 7,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('39 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('39 days ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://x.com/',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 29,
        'visitor' => 7,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('22 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('22 days ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 30,
        'visitor' => 8,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('3 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('3 days ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://www.facebook.com/in2code.de',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 31,
        'visitor' => 8,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('3 days ago'))->modify('+ 4 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('3 days ago'))->modify('+ 4 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://www.perplexity.ai/',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 32,
        'visitor' => 8,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('3 days ago'))->modify('+ 8 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('3 days ago'))->modify('+ 8 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 33,
        'visitor' => 9,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('2 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('2 days ago'))->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://x.com/',
        'domain' => 'landingpage.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 34,
        'visitor' => 10,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('1 days ago'))->modify('+3 hours')->getTimestamp(),
        'tstamp' => (new DateTime('1 days ago'))->modify('+3 hours')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => 'https://x.com/',
        'domain' => 'landingpage.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 35,
        'visitor' => 11,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('4 hours ago'))->modify('+12 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('4 hours ago'))->modify('+12 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 36,
        'visitor' => 12,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('3 hours ago'))->modify('+32 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('3 hours ago'))->modify('+32 minutes')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
    [
        'uid' => 37,
        'visitor' => 13,
        'page' => DemoDataService::getRandomPageIdentifier(),
        'crdate' => (new DateTime('12 days ago'))->modify('+7 hours')->getTimestamp(),
        'tstamp' => (new DateTime('12 days ago'))->modify('+7 hours')->getTimestamp(),
        'sys_language_uid' => -1,
        'referrer' => '',
        'domain' => 'demodata.com',
        'site' => DemoDataService::getRandomSiteIdentifier(),
    ],
];
