<?php
declare(strict_types=1);
namespace In2code\Lux\Widgets\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\Interfaces\ChartDataProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class LuxIdentifiedDataProvider
 * @noinspection PhpUnused
 */
class LuxIdentifiedDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    public function getChartData(): array
    {
        $llPrefix = 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:';
        $label = LocalizationUtility::getLanguageService()->sL(
            $llPrefix . 'module.dashboard.widget.luxidentified.label'
        );
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        return [
            'labels' => [
                LocalizationUtility::getLanguageService()->sL(
                    $llPrefix . 'module.dashboard.widget.luxidentified.label.0'
                ),
                LocalizationUtility::getLanguageService()->sL(
                    $llPrefix . 'module.dashboard.widget.luxidentified.label.1'
                ),
                LocalizationUtility::getLanguageService()->sL(
                    $llPrefix . 'module.dashboard.widget.luxidentified.label.2'
                ),
            ],
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => [WidgetApi::getDefaultChartColors()[0], '#dddddd'],
                    'border' => 0,
                    'data' => [
                        $visitorRepository->findAllIdentifiedAmount(),
                        $visitorRepository->findAllUnknownAmount(),
                        $visitorRepository->findAllAmount()
                    ]
                ]
            ]
        ];
    }
}
