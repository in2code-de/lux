<?php

declare(strict_types=1);
namespace In2code\Lux\Widgets\DataProvider;

use In2code\Lux\Domain\DataProvider\PagevisistsDataProvider;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;

class LuxPageVisitsWeekDataProvider implements ChartDataProviderInterface
{
    public function getChartData(): array
    {
        $pagevisistsData = GeneralUtility::makeInstance(PagevisistsDataProvider::class, ObjectUtility::getFilterDto());
        return [
            'labels' => $pagevisistsData->getTitlesFromData(),
            'datasets' => [
                [
                    'label' => LocalizationUtility::translateByKey('module.dashboard.widget.luxpagevisitsweek.label'),
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
                    'data' => $pagevisistsData->getAmountsFromData(),
                ],
            ],
        ];
    }
}
