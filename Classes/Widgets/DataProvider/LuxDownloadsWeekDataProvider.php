<?php

declare(strict_types=1);
namespace In2code\Lux\Widgets\DataProvider;

use Exception;
use In2code\Lux\Domain\DataProvider\DownloadsDataProvider;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;

/**
 * Class LuxDownloadsWeekDataProvider
 * @noinspection PhpUnused
 */
class LuxDownloadsWeekDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     * @throws Exception
     */
    public function getChartData(): array
    {
        $downloadsData = GeneralUtility::makeInstance(DownloadsDataProvider::class, ObjectUtility::getFilterDto());
        return [
            'labels' => $downloadsData->getTitlesFromData(),
            'datasets' => [
                [
                    'label' => LocalizationUtility::translateByKey('module.dashboard.widget.luxdownloadsweek.label'),
                    'backgroundColor' => [
                        '#dddddd',
                        '#dddddd',
                        '#dddddd',
                        '#dddddd',
                        '#dddddd',
                        '#dddddd',
                        '#dddddd',
                        WidgetApi::getDefaultChartColors()[0],
                    ],
                    'border' => 0,
                    'data' => $downloadsData->getAmountsFromData(),
                ],
            ],
        ];
    }
}
