<?php

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

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
        'source' => 'EXT:lux/Resources/Public/Icons/lux_module_analysis.svg',
    ],
    'extension-lux-module-lead' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:lux/Resources/Public/Icons/lux_module_lead.svg',
    ],
    'extension-lux-module-workflow' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:lux/Resources/Public/Icons/lux_module_workflow.svg',
    ],
    'extension-lux-star' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:lux/Resources/Public/Icons/star.svg',
    ],
];
