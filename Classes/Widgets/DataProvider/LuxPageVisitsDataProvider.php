<?php
declare(strict_types=1);
namespace In2code\Lux\Widgets\DataProvider;

use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\Interfaces\ChartDataProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class LuxPageVisitsDataProvider
 * @noinspection PhpUnused
 */
class LuxPageVisitsDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     * @throws Exception
     * @throws InvalidQueryException
     */
    public function getChartData(): array
    {
        $data = $this->getPageData();
        $label = LocalizationUtility::getLanguageService()->sL(
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxpagevisits.label'
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
     *          88,
     *          45
     *      ],
     *      'titles' => [
     *          'Home (id=123)',
     *          'Page 1 (id=25)',
     *          'Page 2 (id=13)',
     *      ]
     *  ]
     *
     * @return array
     * @throws Exception
     * @throws InvalidQueryException
     */
    protected function getPageData(): array
    {
        $pagevisitRepository = ObjectUtility::getObjectManager()->get(PagevisitRepository::class);
        $pageRepository = ObjectUtility::getObjectManager()->get(PageRepository::class);
        $pageVisits = $pagevisitRepository->findCombinedByPageIdentifier(
            ObjectUtility::getFilterDto(FilterDto::PERIOD_THISYEAR)
        );
        $titles = $amounts = [];
        for ($i = 0; $i < 6; $i++) {
            if (!empty($pageVisits[$i][0]['page'])) {
                /** @var Page $page */
                $page = $pageRepository->findByIdentifier($pageVisits[$i][0]['page']);
                $titles[] = $page->getTitle() . ' (id=' . $page->getUid() . ')';
                $amounts[] = count($pageVisits[$i]);
            }
        }
        return ['amounts' => $amounts, 'titles' => $titles];
    }
}
