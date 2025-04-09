<?php

use In2code\Lux\Domain\Service\DemoDataService;

return [
    [
        'uid' => 1,
        'tstamp' => (new DateTime())->getTimestamp(),
        'crdate' => (new DateTime())->getTimestamp(),
        'sys_language_uid' => -1,
        'title' => 'Klick auf Button in Bühne',
        'link' => 't3://page?uid=' . DemoDataService::getRandomPageIdentifier(),
        'category' => DemoDataService::getRandomLuxCatgoryIdentifier(),
        'description' => 'Check stage performance by clicks on the big "call to action"',
    ],
    [
        'uid' => 2,
        'tstamp' => (new DateTime())->getTimestamp(),
        'crdate' => (new DateTime())->getTimestamp(),
        'sys_language_uid' => -1,
        'title' => 'Landingpage Performance',
        'link' => 't3://page?uid=' . DemoDataService::getRandomPageIdentifier(),
        'category' => DemoDataService::getRandomLuxCatgoryIdentifier(),
        'description' => 'Check performance of landing page',
    ],
    [
        'uid' => 3,
        'tstamp' => (new DateTime())->getTimestamp(),
        'crdate' => (new DateTime())->getTimestamp(),
        'sys_language_uid' => -1,
        'title' => 'Product A - Contactform',
        'link' => 't3://page?uid=' . DemoDataService::getFirstRootPageIdentifier(),
        'category' => DemoDataService::getRandomLuxCatgoryIdentifier(),
        'description' => 'Check performance contactform link on product page',
    ],
];
