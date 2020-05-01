<?php
declare(strict_types=1);
namespace In2code\Lux\Widgets\DataProvider;

use In2code\Lux\Domain\DataProvider\ReferrerAmountDataProvider;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class LuxReferrerDataProvider
 * @noinspection PhpUnused
 */
class LuxReferrerDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     * @throws Exception
     */
    public function getChartData(): array
    {
        $referrerAmountDP = ObjectUtility::getObjectManager()->get(ReferrerAmountDataProvider::class);
        return [
            'labels' => $referrerAmountDP->getData()['titles'],
            'datasets' => [
                [
                    'label' => $this->getLabel(),
                    'backgroundColor' => [WidgetApi::getDefaultChartColors()[0], '#dddddd'],
                    'border' => 0,
                    'data' => $referrerAmountDP->getData()['amounts']
                ]
            ]
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
