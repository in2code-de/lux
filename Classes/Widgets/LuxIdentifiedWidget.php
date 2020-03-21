<?php
declare(strict_types=1);
namespace In2code\Lux\Widgets;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\Widgets\AbstractBarChartWidget;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class LuxIdentifiedWidget
 * @noinspection PhpUnused
 */
class LuxIdentifiedWidget extends AbstractBarChartWidget
{
    protected $title =
        'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxidentified.title';
    protected $description =
        'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxidentified.description';
    protected $iconIdentifier = 'extension-lux-turquoise';
    protected $height = 4;
    protected $width = 2;

    /**
     * @return void
     * @throws DBALException
     * @throws Exception
     */
    protected function prepareChartData(): void
    {
        $llPrefix = 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:';
        $label = LocalizationUtility::getLanguageService()->sL(
            $llPrefix . 'module.dashboard.widget.luxidentified.label'
        );
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $this->chartData = [
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
                    'backgroundColor' => [$this->chartColors[0], '#dddddd'],
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
