<?php

declare(strict_types=1);
namespace In2code\Lux\Widgets\DataProvider;

use In2code\Lux\Domain\DataProvider\UtmMediaDataProvider;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;

class LuxUtmMediaDataProvider implements ChartDataProviderInterface
{
    public function getChartData(): array
    {
        $data = GeneralUtility::makeInstance(UtmMediaDataProvider::class, ObjectUtility::getFilterDto());
        return [
            'labels' => $data->getTitlesFromData(),
            'datasets' => [
                [
                    'label' => LocalizationUtility::translateByKey('module.dashboard.widget.luxpagevisitsweek.label'),
                    'backgroundColor' => WidgetApi::getDefaultChartColors(),
                    'border' => 0,
                    'data' => $data->getAmountsFromData(),
                ],
            ],
        ];
    }
}
