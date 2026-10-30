<?php

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

// Todo: Remove once TYPO3 13 support is dropped
$moduleIconSuffix = GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() === 13 ? '_v13' : '';

return [
    'extension-lux-module' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:lux/Resources/Public/Icons/lux_white.svg',
    ],
    'extension-lux' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:lux/Resources/Public/Icons/lux.svg',
    ],
    'extension-lux-turquoise' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:lux/Resources/Public/Icons/Extension.svg',
    ],
    'extension-lux-module-analysis' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:lux/Resources/Public/Icons/lux_module_analysis' . $moduleIconSuffix . '.svg',
    ],
    'extension-lux-module-lead' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:lux/Resources/Public/Icons/lux_module_lead' . $moduleIconSuffix . '.svg',
    ],
    'extension-lux-module-workflow' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:lux/Resources/Public/Icons/lux_module_workflow' . $moduleIconSuffix . '.svg',
    ],
    'extension-lux-star' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:lux/Resources/Public/Icons/star.svg',
    ],
];
