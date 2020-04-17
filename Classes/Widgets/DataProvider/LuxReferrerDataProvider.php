<?php
declare(strict_types=1);
namespace In2code\Lux\Widgets\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\Interfaces\ChartDataProviderInterface;
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
     * @throws DBALException
     */
    public function getChartData(): array
    {
        $llPrefix = 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:';
        $label = LocalizationUtility::getLanguageService()->sL(
            $llPrefix . 'module.dashboard.widget.referrer.label'
        );
        return [
            'labels' => $this->getReferrerData()['titles'],
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => [WidgetApi::getDefaultChartColors()[0], '#dddddd'],
                    'border' => 0,
                    'data' => $this->getReferrerData()['amounts']
                ]
            ]
        ];
    }

    /**
     *  [
     *      'amounts' => [
     *          120,
     *          88
     *      ],
     *      'titles' => [
     *          '/fileadmin/user_upload/whitepaper.pdf',
     *          '/fileadmin/whitepaperProductX.pdf',
     *      ]
     *  ]
     *
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    protected function getReferrerData(): array
    {
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_THISYEAR);
        $referrers = $visitorRepository->getAmountOfReferrers($filter);
        $titles = $amounts = [];
        $counter = 0;
        foreach ($referrers as $referrer => $amount) {
            $titles[] = $referrer;
            $amounts[] = $amount;
            if ($counter >= 5) {
                break;
            }
            $counter++;
        }
        return ['amounts' => $amounts, 'titles' => $titles];
    }
}
