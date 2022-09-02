<?php

declare(strict_types=1);
namespace In2code\Lux\Widgets\DataProvider;

use In2code\Lux\Domain\DataProvider\BrowserAmountDataProvider;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;

/**
 * Class LuxBrowserDataProvider
 * @noinspection PhpUnused
 */
class LuxBrowserDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     */
    public function getChartData(): array
    {
        $browserAmountDP = GeneralUtility::makeInstance(BrowserAmountDataProvider::class);
        return [
            'labels' => $browserAmountDP->getTitlesFromData(),
            'datasets' => [
                [
                    'label' => $this->getWidgetLabel(),
                    'backgroundColor' => [
                        WidgetApi::getDefaultChartColors()[0],
                        WidgetApi::getDefaultChartColors()[1],
                        WidgetApi::getDefaultChartColors()[2],
                        WidgetApi::getDefaultChartColors()[3],
                        WidgetApi::getDefaultChartColors()[4],
                        '#dddddd',
                    ],
                    'border' => 0,
                    'data' => $browserAmountDP->getAmountsFromData(),
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getWidgetLabel(): string
    {
        return LocalizationUtility::getLanguageService()->sL(
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.browser.label'
        );
    }
}
