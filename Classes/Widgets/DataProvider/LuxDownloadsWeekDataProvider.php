<?php
declare(strict_types = 1);
namespace In2code\Lux\Widgets\DataProvider;

use In2code\Lux\Domain\DataProvider\DownloadsDataProvider;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class LuxDownloadsWeekDataProvider
 * @noinspection PhpUnused
 */
class LuxDownloadsWeekDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     * @throws Exception
     * @throws InvalidQueryException
     * @throws \Exception
     */
    public function getChartData(): array
    {
        $downloadsData = ObjectUtility::getObjectManager()->get(
            DownloadsDataProvider::class,
            ObjectUtility::getFilterDto()
        );
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
                        WidgetApi::getDefaultChartColors()[0]
                    ],
                    'border' => 0,
                    'data' => $downloadsData->getAmountsFromData()
                ]
            ]
        ];
    }
}
