<?php
declare(strict_types=1);
namespace In2code\Lux\Widgets;

use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\Widgets\AbstractBarChartWidget;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class LuxPageVisitsWeekWidget
 * @noinspection PhpUnused
 */
class LuxPageVisitsWeekWidget extends AbstractBarChartWidget
{
    protected $title =
        'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxpagevisitsweek.title';
    protected $description =
        'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxpagevisitsweek.description';
    protected $iconIdentifier = 'extension-lux-turquoise';
    protected $height = 4;
    protected $width = 4;

    /**
     * @return void
     * @throws Exception
     * @throws InvalidQueryException
     */
    protected function prepareChartData(): void
    {
        $pagevisitRepository = ObjectUtility::getObjectManager()->get(PagevisitRepository::class);
        $data = $pagevisitRepository->getNumberOfVisitorsByDay();
        $label = LocalizationUtility::getLanguageService()->sL(
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxpagevisitsweek.label'
        );
        $this->chartData = [
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
                        $this->chartColors[0]
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
