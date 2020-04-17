<?php
declare(strict_types=1);
namespace In2code\Lux\Widgets\DataProvider;

use In2code\Lux\Domain\Repository\DownloadRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\Interfaces\ChartDataProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class LuxPageVisitsWeekDataProvider
 * @noinspection PhpUnused
 */
class LuxPageVisitsWeekDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     * @throws Exception
     * @throws InvalidQueryException
     */
    public function getChartData(): array
    {
        $downloadRepository = ObjectUtility::getObjectManager()->get(DownloadRepository::class);
        $data = $downloadRepository->getNumberOfDownloadsByDay();
        $label = LocalizationUtility::getLanguageService()->sL(
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxdownloadsweek.label'
        );
        return [
            'labels' => $this->getLabels(),
            'datasets' => [
                [
                    'label' => $label,
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
                    'data' => array_values($data)
                ]
            ]
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getLabels(): array
    {
        $weekdayNames = [];
        $locallangPrefix = 'datetime.weekday.';
        $weekdayNames[] = LocalizationUtility::translateByKey($locallangPrefix . 'now');
        $yesterday = new \DateTime('yesterday');
        $weekdayNames[] = LocalizationUtility::translateByKey($locallangPrefix . strtolower($yesterday->format('D')));
        $day3 = new \DateTime('2 days ago');
        $weekdayNames[] = LocalizationUtility::translateByKey($locallangPrefix . strtolower($day3->format('D')));
        $day4 = new \DateTime('3 days ago');
        $weekdayNames[] = LocalizationUtility::translateByKey($locallangPrefix . strtolower($day4->format('D')));
        $day5 = new \DateTime('4 days ago');
        $weekdayNames[] = LocalizationUtility::translateByKey($locallangPrefix . strtolower($day5->format('D')));
        $day6 = new \DateTime('5 days ago');
        $weekdayNames[] = LocalizationUtility::translateByKey($locallangPrefix . strtolower($day6->format('D')));
        $day7 = new \DateTime('6 days ago');
        $weekdayNames[] = LocalizationUtility::translateByKey($locallangPrefix . strtolower($day7->format('D')));
        $day8 = new \DateTime('7 days ago');
        $weekdayNames[] = LocalizationUtility::translateByKey($locallangPrefix . strtolower($day8->format('D')));
        $weekdayNames = array_reverse($weekdayNames);
        return $weekdayNames;
    }
}
