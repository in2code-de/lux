<?php

return [
    [
        'crdate' => (new DateTime('31 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('2 hours ago'))->modify('+ 222 minutes')->getTimestamp(),
        'uid' => 1,
        'email' => 'stefan.busemann@in2code.de',
        'description' => 'Stefan Busemann bei in2code',
        'identified' => 1,
        'ip_address' => '127.0.0.1',
        'visits' => 6,
        'scoring' => 66,
        'sys_language_uid' => -1,
        'company' => 'in2code GmbH',
        'companyrecord' => 1,
        'fingerprints' => 1,
    ],
    [
        'crdate' => (new DateTime('47 days ago'))->modify('+ 555 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('2 hours ago'))->getTimestamp(),
        'uid' => 2,
        'email' => 'alex@kellner.com',
        'description' => '',
        'identified' => 1,
        'ip_address' => '217.72.208.133',
        'visits' => 4,
        'scoring' => 50,
        'sys_language_uid' => -1,
        'company' => 'Kellner Logistics',
        'companyrecord' => 2,
        'fingerprints' => 1,
    ],
    [
        'crdate' => (new DateTime('55 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('3 days ago'))->modify('+ 222 minutes')->getTimestamp(),
        'uid' => 3,
        'email' => '',
        'description' => '',
        'identified' => 0,
        'ip_address' => '',
        'visits' => 2,
        'scoring' => 35,
        'sys_language_uid' => -1,
        'company' => 'Bad-Mainhoffer Klinikum AG',
        'companyrecord' => 3,
        'fingerprints' => 1,
    ],
    [
        'crdate' => (new DateTime('130 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('1 days ago'))->modify('+ 543 minutes')->getTimestamp(),
        'uid' => 4,
        'email' => 'max.muster@university.de',
        'description' => 'PR of university',
        'identified' => 1,
        'ip_address' => '',
        'visits' => 3,
        'scoring' => 22,
        'sys_language_uid' => -1,
        'company' => 'Université De Vienna',
        'companyrecord' => 4,
        'fingerprints' => 1,
    ],
    [
        'crdate' => (new DateTime('55 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('55 days ago'))->getTimestamp(),
        'uid' => 5,
        'email' => 'john.doe@umbrella.com',
        'description' => 'CTO of Umbrella Corporation',
        'identified' => 1,
        'ip_address' => '',
        'visits' => 1,
        'scoring' => 5,
        'sys_language_uid' => -1,
        'company' => 'Umbrella Corporation',
        'companyrecord' => 5,
        'fingerprints' => 1,
    ],
    [
        'crdate' => (new DateTime('188 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('6 days ago'))->getTimestamp(),
        'uid' => 6,
        'email' => 'richter.david@wettbewerber.org',
        'description' => 'Neugieriger Sales vom Wettbewerber',
        'identified' => 1,
        'ip_address' => '',
        'visits' => 2,
        'scoring' => 20,
        'sys_language_uid' => -1,
        'company' => 'Wettbewerber AG',
        'companyrecord' => 6,
        'fingerprints' => 1,
    ],
    [
        'crdate' => (new DateTime('39 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('22 days ago'))->getTimestamp(),
        'uid' => 7,
        'description' => '',
        'ip_address' => '122.234.0.5',
        'visits' => 2,
        'scoring' => 21,
        'sys_language_uid' => -1,
        'company' => 'Netprime Plus - TV streaming company',
        'companyrecord' => 7,
        'fingerprints' => 1,
    ],
    [
        'crdate' => (new DateTime('3 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('3 days ago'))->getTimestamp(),
        'uid' => 8,
        'description' => '',
        'ip_address' => '',
        'visits' => 1,
        'scoring' => 20,
        'sys_language_uid' => -1,
        'company' => 'University Switzerland',
        'companyrecord' => 8,
        'fingerprints' => 1,
    ],
    [
        'crdate' => (new DateTime('2 days ago'))->getTimestamp(),
        'tstamp' => (new DateTime('2 days ago'))->getTimestamp(),
        'uid' => 9,
        'description' => '',
        'ip_address' => '',
        'visits' => 1,
        'scoring' => 16,
        'sys_language_uid' => -1,
        'company' => 'Real Estate Professionals',
        'companyrecord' => 9,
        'fingerprints' => 1,
    ],
    [
        'crdate' => (new DateTime('1 days ago'))->modify('+3 hours')->getTimestamp(),
        'tstamp' => (new DateTime('1 days ago'))->modify('+3 hours')->getTimestamp(),
        'uid' => 10,
        'description' => '',
        'ip_address' => '',
        'visits' => 1,
        'scoring' => 11,
        'sys_language_uid' => -1,
        'fingerprints' => 1,
    ],
    [
        'crdate' => (new DateTime('4 hours ago'))->modify('+12 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('4 hours ago'))->modify('+12 minutes')->getTimestamp(),
        'uid' => 11,
        'description' => '',
        'ip_address' => '',
        'visits' => 1,
        'scoring' => 11,
        'sys_language_uid' => -1,
        'fingerprints' => 1,
    ],
    [
        'crdate' => (new DateTime('3 hours ago'))->modify('+32 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('3 hours ago'))->modify('+32 minutes')->getTimestamp(),
        'uid' => 12,
        'description' => '',
        'ip_address' => '',
        'visits' => 1,
        'scoring' => 11,
        'sys_language_uid' => -1,
        'fingerprints' => 1,
    ],
    [
        'crdate' => (new DateTime('12 days ago'))->modify('+7 hours')->getTimestamp(),
        'tstamp' => (new DateTime('12 days ago'))->modify('+7 hours')->getTimestamp(),
        'uid' => 13,
        'description' => '',
        'ip_address' => '',
        'visits' => 1,
        'scoring' => 11,
        'sys_language_uid' => -1,
        'fingerprints' => 1,
    ],
];
