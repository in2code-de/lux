<?php
declare(strict_types = 1);
namespace In2code\Lux\Widgets\DataProvider;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class LuxPageVisitsDataProvider
 * @noinspection PhpUnused
 */
class LuxPageVisitsDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     * @throws Exception
     * @throws ExceptionDbal
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
     * @throws ExceptionDbal
     */
    protected function getPageData(): array
    {
        $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
        $pageVisits = $pagevisitRepository->findCombinedByPageIdentifier(
            ObjectUtility::getFilterDto(FilterDto::PERIOD_THISYEAR)
        );
        $titles = $amounts = [];
        for ($i = 0; $i < 6; $i++) {
            if (!empty($pageVisits[$i])) {
                /** @var Page $page */
                $title = StringUtility::cropString($pageVisits[$i]['page']['title'], 40)
                    . ' (id=' . $pageVisits[$i]['page']['uid'] . ')';
                $titles[] = $title;
                $amounts[] = $pageVisits[$i]['count'];
            }
        }
        return ['amounts' => $amounts, 'titles' => $titles];
    }
}
