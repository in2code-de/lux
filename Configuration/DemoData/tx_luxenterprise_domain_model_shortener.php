<?php

use In2code\Lux\Domain\Service\DemoDataService;

return [
    [
        'shortenervisits' => 1,
        'tstamp' => (new DateTime())->getTimestamp(),
        'crdate' => (new DateTime())->getTimestamp(),
        'from' => 'landingpage',
        'to' => 'https://www.linkedin.com',
        'category' => DemoDataService::getRandomLuxCatgoryIdentifier(),
        'description' => 'Shortener to landingpage',
    ],
    [
        'shortenervisits' => 3,
        'tstamp' => (new DateTime())->getTimestamp(),
        'crdate' => (new DateTime())->getTimestamp(),
        'from' => 'blogpost',
        'to' => 't3://page?uid=' . DemoDataService::getRandomPageIdentifier(),
        'category' => DemoDataService::getRandomLuxCatgoryIdentifier(),
        'description' => 'Shortener to new blog post',
    ],
];
