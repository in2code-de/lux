<?php
declare(strict_types=1);
namespace In2code\Lux\Widgets;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\Widgets\AbstractDoughnutChartWidget;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class LuxRecurringWidget
 * @noinspection PhpUnused
 */
class LuxRecurringWidget extends AbstractDoughnutChartWidget
{
    protected $title =
        'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxrecurring.title';
    protected $description =
        'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxrecurring.description';
    protected $iconIdentifier = 'extension-lux-turquoise';
    protected $height = 4;
    protected $width = 2;

    /**
     * @var array
     */
    protected $chartOptions = [
        'maintainAspectRatio' => false,
        'legend' => [
            'display' => true,
            'position' => 'right'
        ],
        'cutoutPercentage' => 40
    ];

    /**
     * @return void
     * @throws DBALException
     * @throws Exception
     * @throws InvalidQueryException
     */
    protected function prepareChartData(): void
    {
        $llPrefix = 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:';
        $label = LocalizationUtility::getLanguageService()->sL(
            $llPrefix . 'module.dashboard.widget.luxrecurring.label'
        );
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_THISYEAR);
        $this->chartData = [
            'labels' => [
                LocalizationUtility::getLanguageService()->sL(
                    $llPrefix . 'module.dashboard.widget.luxrecurring.label.0'
                ),
                LocalizationUtility::getLanguageService()->sL(
                    $llPrefix . 'module.dashboard.widget.luxrecurring.label.1'
                )
            ],
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => [$this->chartColors[0], '#dddddd'],
                    'border' => 0,
                    'data' => [
                        $visitorRepository->findByRecurringSiteVisits($filter)->count(),
                        $visitorRepository->findByUniqueSiteVisits($filter)->count()
                    ]
                ]
            ]
        ];
    }
}
