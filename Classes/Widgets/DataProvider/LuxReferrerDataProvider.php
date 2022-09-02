<?php

declare(strict_types=1);
namespace In2code\Lux\Widgets\DataProvider;

use In2code\Lux\Domain\DataProvider\ReferrerAmountDataProvider;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;

/**
 * Class LuxReferrerDataProvider
 * @noinspection PhpUnused
 */
class LuxReferrerDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     */
    public function getChartData(): array
    {
        $referrerAmountDP = GeneralUtility::makeInstance(ReferrerAmountDataProvider::class);
        return [
            'labels' => $referrerAmountDP->getTitlesFromData(),
            'datasets' => [
                [
                    'label' => $this->getLabel(),
                    'backgroundColor' => [WidgetApi::getDefaultChartColors()[0], '#dddddd'],
                    'border' => 0,
                    'data' => $referrerAmountDP->getAmountsFromData(),
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getLabel(): string
    {
        return LocalizationUtility::getLanguageService()->sL(
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.referrer.label'
        );
    }
}
