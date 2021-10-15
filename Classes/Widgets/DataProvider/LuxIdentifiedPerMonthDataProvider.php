<?php
declare(strict_types = 1);
namespace In2code\Lux\Widgets\DataProvider;

use DateTime;
use Doctrine\DBAL\DBALException;
use Exception;
use In2code\Lux\Domain\Repository\LogRepository;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;

/**
 * Class LuxIdentifiedPerMonthDataProvider
 * @noinspection PhpUnused
 */
class LuxIdentifiedPerMonthDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    public function getChartData(): array
    {
        $llPrefix = 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:';
        $label = LocalizationUtility::getLanguageService()->sL(
            $llPrefix . 'module.dashboard.widget.luxidentified.label'
        );
        return [
            'labels' => $this->getMonthNames(),
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => [
                        '#dddddd',
                        '#dddddd',
                        '#dddddd',
                        '#dddddd',
                        '#dddddd',
                        WidgetApi::getDefaultChartColors()[0]
                    ],
                    'border' => 0,
                    'data' => $this->getData()
                ]
            ]
        ];
    }

    /**
     * @return array
     * @throws DBALException
     */
    protected function getData(): array
    {
        $logRepository = GeneralUtility::makeInstance(LogRepository::class);
        $logs = $logRepository->findIdentifiedLogsFromMonths(6);
        $amount = [];
        foreach ($logs as $logsCombined) {
            $amount[] = count($logsCombined);
        }
        return $amount;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getMonthNames(): array
    {
        $now = new DateTime();
        $monthNames = [
            LocalizationUtility::translateByKey('datetime.month.' . $now->format('n'))
        ];
        for ($i = 1; $i < 6; $i++) {
            $month = clone $now;
            $month->modify('-' . $i . ' months');
            $monthNames[] = LocalizationUtility::translateByKey('datetime.month.' . $month->format('n'));
        }
        return array_reverse($monthNames);
    }
}
