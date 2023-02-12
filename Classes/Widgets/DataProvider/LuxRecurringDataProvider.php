<?php

declare(strict_types=1);
namespace In2code\Lux\Widgets\DataProvider;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

class LuxRecurringDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     * @throws InvalidQueryException
     */
    public function getChartData(): array
    {
        $llPrefix = 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:';
        $label = LocalizationUtility::getLanguageService()->sL(
            $llPrefix . 'module.dashboard.widget.luxrecurring.label'
        );
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_THISYEAR);
        return [
            'labels' => [
                LocalizationUtility::getLanguageService()->sL(
                    $llPrefix . 'module.dashboard.widget.luxrecurring.label.0'
                ),
                LocalizationUtility::getLanguageService()->sL(
                    $llPrefix . 'module.dashboard.widget.luxrecurring.label.1'
                ),
            ],
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => [WidgetApi::getDefaultChartColors()[0], '#dddddd'],
                    'border' => 0,
                    'data' => [
                        $visitorRepository->findByRecurringSiteVisits($filter)->count(),
                        $visitorRepository->findByUniqueSiteVisits($filter)->count(),
                    ],
                ],
            ],
        ];
    }
}
