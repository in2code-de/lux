<?php

return [
    [
        'visitor' => 1,
        'shortener' => 1,
        'crdate' => (new DateTime('4 days ago'))->modify('+342 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('4 days ago'))->modify('+342 minutes')->getTimestamp(),
        'referrer' => 'https://l.facebook.com/r.php?test',
    ],
    [
        'visitor' => 2,
        'shortener' => 2,
        'crdate' => (new DateTime('14 days ago'))->modify('+542 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('14 days ago'))->modify('+542 minutes')->getTimestamp(),
        'referrer' => 'https://vimeo.com/400360913',
    ],
    [
        'visitor' => 3,
        'shortener' => 2,
        'crdate' => (new DateTime('2 days ago'))->modify('+678 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('2 days ago'))->modify('+678 minutes')->getTimestamp(),
        'referrer' => 'https://vimeo.com/400360913',
    ],
    [
        'visitor' => 8,
        'shortener' => 2,
        'crdate' => (new DateTime('2 days ago'))->modify('+12 minutes')->getTimestamp(),
        'tstamp' => (new DateTime('2 days ago'))->modify('+12 minutes')->getTimestamp(),
        'referrer' => 'https://vimeo.com/400360913',
    ],
];
