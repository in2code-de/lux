<?php
declare(strict_types = 1);
namespace In2code\Lux\Widgets\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\NewsvisitRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class LuxNewsDataProvider
 * @noinspection PhpUnused
 */
class LuxNewsDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    public function getChartData(): array
    {
        $data = $this->getNewsData();
        $label = LocalizationUtility::getLanguageService()->sL(
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxnews.label'
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
     *          'Hot stuff is going on with TYPO3',
     *          'News February 2022: New car...',
     *      ]
     *  ]
     *
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    protected function getNewsData(): array
    {
        $newsvisitRepository = ObjectUtility::getObjectManager()->get(NewsvisitRepository::class);
        $news = $newsvisitRepository->findCombinedByNewsIdentifier(
            ObjectUtility::getFilterDto(FilterDto::PERIOD_THISYEAR)
        );
        $titles = $amounts = [];
        $counter = 0;
        foreach ($news as $newsItem) {
            if ($counter > 5) {
                break;
            }
            $counter++;
            $titles[] = StringUtility::cropString($newsItem['news']->getTitle(), 40)
                . ' (id=' . $newsItem['news']->getUid() . ')';
            $amounts[] = $newsItem['count'];
        }
        return ['amounts' => $amounts, 'titles' => $titles];
    }
}
