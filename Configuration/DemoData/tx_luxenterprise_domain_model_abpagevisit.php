<?php

use In2code\Lux\Domain\Service\DemoDataService;

return [
    [
        'tstamp' => (new DateTime('2 hours ago'))->modify('+ 34 minutes')->getTimestamp(),
        'crdate' => (new DateTime('2 hours ago'))->modify('+ 34 minutes')->getTimestamp(),
        'visitor' => 1,
        'abpage' => DemoDataService::getRandomAbPage(),
        'conversion_fulfilled' => 1,
    ],
    [
        'tstamp' => (new DateTime('12 hours ago'))->modify('+ 314 minutes')->getTimestamp(),
        'crdate' => (new DateTime('12 hours ago'))->modify('+ 314 minutes')->getTimestamp(),
        'visitor' => 2,
        'abpage' => DemoDataService::getRandomAbPage(),
        'conversion_fulfilled' => 1,
    ],
    [
        'tstamp' => (new DateTime('12 hours ago'))->modify('+ 34 minutes')->getTimestamp(),
        'crdate' => (new DateTime('12 hours ago'))->modify('+ 34 minutes')->getTimestamp(),
        'visitor' => 3,
        'abpage' => DemoDataService::getRandomAbPage(),
        'conversion_fulfilled' => 0,
    ],
    [
        'tstamp' => (new DateTime('22 hours ago'))->modify('+ 34 minutes')->getTimestamp(),
        'crdate' => (new DateTime('22 hours ago'))->modify('+ 34 minutes')->getTimestamp(),
        'visitor' => 4,
        'abpage' => DemoDataService::getRandomAbPage(),
        'conversion_fulfilled' => 0,
    ],
    [
        'tstamp' => (new DateTime('1 hours ago'))->modify('+ 34 minutes')->getTimestamp(),
        'crdate' => (new DateTime('1 hours ago'))->modify('+ 34 minutes')->getTimestamp(),
        'visitor' => 5,
        'abpage' => DemoDataService::getRandomAbPage(),
        'conversion_fulfilled' => 0,
    ],
];
