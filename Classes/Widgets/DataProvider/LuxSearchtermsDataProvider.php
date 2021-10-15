<?php
declare(strict_types = 1);
namespace In2code\Lux\Widgets\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\SearchRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;

/**
 * Class LuxSearchtermsDataProvider
 * @noinspection PhpUnused
 */
class LuxSearchtermsDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     * @throws DBALException
     */
    public function getChartData(): array
    {
        $data = $this->getData();
        $label = LocalizationUtility::getLanguageService()->sL(
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxsearchterms.label'
        );
        return [
            'labels' => $data['titles'],
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => [WidgetApi::getDefaultChartColors()[0], '#dddddd'],
                    'border' => 0,
                    'data' => $data['amounts']
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
     *          'lux',
     *          'luxletter',
     *      ]
     *  ]
     *
     * @return array
     * @throws DBALException
     */
    protected function getData(): array
    {
        $searchRepository = GeneralUtility::makeInstance(SearchRepository::class);
        $terms =
            $searchRepository->findCombinedBySearchIdentifier(ObjectUtility::getFilterDto(FilterDto::PERIOD_THISYEAR));
        $titles = $amounts = [];
        $counter = 0;
        foreach ($terms as $term) {
            if ($counter > 5) {
                break;
            }
            $counter++;
            $titles[] = StringUtility::cropString($term['searchterm'], 40);
            $amounts[] = $term['count'];
        }
        return ['amounts' => $amounts, 'titles' => $titles];
    }
}
