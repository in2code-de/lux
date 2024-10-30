<?php

$additional = [
    'BE' => [
        'installToolPassword' => '$argon2i$v=19$m=65536,t=16,p=1$RmZtaE5LQU1rSGw2NUZiWQ$YdU5on+xJ4lI6Gwd4LWpbddeAEu88cctS2dnO+r9ty0',
        'lockSSL' => '0',
        'compressionLevel' => '0',
        'debug' => true,
        'loginRateLimit' => 1000,
    ],
    'DB' => [
        'Connections' => [
            'Default' => [
                'charset' => 'utf8',
                'driver' => 'mysqli',
                'dbname' => getenv('MYSQL_DATABASE'),
                'host' => getenv('MYSQL_HOST'),
                'user' => getenv('MYSQL_USER'),
                'password' => getenv('MYSQL_PASSWORD'),
            ],
        ],
    ],
    'FE' => [
        'cacheHash' => [
            'enforceValidation' => true,
            'excludedParameters' => [
                'L',
                'mtm_campaign',
                'mtm_keyword',
                'pk_campaign',
                'pk_kwd',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
                'utm_content',
                'gclid',
                'fbclid',
                'msclkid',
            ],
        ],
    ],
    'GFX' => [
        'colorspace' => 'sRGB',
        'processor_enabled' => true,
        'processor' => 'ImageMagick',
        'processor_path' => '/usr/bin/',
        'processor_path_lzw' => '/usr/bin/',
    ],
    'HTTP' => [
        'verify' => '0',
    ],
    'MAIL' => [
        'transport_smtp_server' => 'mail:1025',
        'transport_smtp_encrypt' => '',
        'transport_smtp_password' => '',
        'transport_smtp_username' => '',
        'transport' => 'smtp',
        'defaultMailFromAddress' => 'docker@localhost',
        'defaultMailFromName' => 'local - Docker',
    ],
    'SYS' => [
        'sitename' => 'LOKAL: ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
        'displayErrors' => 1,
        'enableDeprecationLog' => 'file',
        'systemLogLevel' => 0,
        'devIPmask' => '*',
        'clearCacheSystem' => 1,
        'curlUse' => 1,
        'exceptionalErrors' => '28674'
    ]
];
$GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive($GLOBALS['TYPO3_CONF_VARS'], $additional);
