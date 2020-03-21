<?php
declare(strict_types=1);
namespace In2code\Lux\Widgets;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\FingerprintRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\Widgets\AbstractDoughnutChartWidget;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class LuxBrowserWidget
 * @noinspection PhpUnused
 */
class LuxBrowserWidget extends AbstractDoughnutChartWidget
{
    protected $title =
        'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.browser.title';
    protected $description =
        'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.browser.description';
    protected $iconIdentifier = 'extension-lux-turquoise';
    protected $height = 4;
    protected $width = 2;

    /**
     * @var array
     */
    protected $browserData = [];

    /**
     * @var array
     */
    protected $chartOptions = [
        'maintainAspectRatio' => false,
        'legend' => [
            'display' => true,
            'position' => 'right'
        ],
        'cutoutPercentage' => 40
    ];

    /**
     * @return void
     * @throws DBALException
     * @throws Exception
     */
    protected function prepareChartData(): void
    {
        $this->chartData = [
            'labels' => $this->getBrowserData()['titles'],
            'datasets' => [
                [
                    'label' => $this->getWidgetLabel('browser.label'),
                    'backgroundColor' => [
                        $this->chartColors[0],
                        $this->chartColors[1],
                        $this->chartColors[2],
                        $this->chartColors[3],
                        $this->chartColors[4],
                        '#dddddd'
                    ],
                    'border' => 0,
                    'data' => $this->getBrowserData()['amounts']
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
     *          'google.com',
     *          'twitter.com',
     *      ]
     *  ]
     *
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    protected function getBrowserData(): array
    {
        if ($this->browserData === []) {
            $fingerprintRepo = ObjectUtility::getObjectManager()->get(FingerprintRepository::class);
            $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_THISYEAR);
            $osBrowsers = $fingerprintRepo->getAmountOfUserAgents($filter);
            $titles = $amounts = [];
            $counter = $additionalAmount = 0;
            foreach ($osBrowsers as $osBrowser => $amount) {
                if ($counter < 5) {
                    $titles[] = $osBrowser;
                    $amounts[] = $amount;
                } else {
                    $additionalAmount += $amount;
                }
                $counter++;
            }
            $titles[] = $this->getWidgetLabel('browser.further');
            $amounts[] = $additionalAmount;
            $this->browserData = ['amounts' => $amounts, 'titles' => $titles];
        }
        return $this->browserData;
    }

    /**
     * @param string $key e.g. "browser.label"
     * @return string
     */
    protected function getWidgetLabel(string $key): string
    {
        $label = LocalizationUtility::getLanguageService()->sL(
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.' . $key
        );
        if (empty($label)) {
            $label = $key;
        }
        return $label;
    }
}
