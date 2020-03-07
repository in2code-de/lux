<?php
declare(strict_types=1);
namespace In2code\Lux\Widgets;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\DownloadRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\Widgets\AbstractBarChartWidget;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class LuxDownloadsWidget
 * @noinspection PhpUnused
 */
class LuxDownloadsWidget extends AbstractBarChartWidget
{
    protected $title =
        'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxdownloads.title';
    protected $description =
        'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxdownloads.description';
    protected $iconIdentifier = 'extension-lux-turquoise';
    protected $height = 4;
    protected $width = 4;

    /**
     * @return void
     * @throws Exception
     * @throws InvalidQueryException
     */
    protected function prepareChartData(): void
    {
        $data = $this->getPageData();
        $label = LocalizationUtility::getLanguageService()->sL(
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxpagevisits.label'
        );
        $this->chartData = [
            'labels' => $data['titles'],
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => [$this->chartColors[0], '#dddddd'],
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
     *          '/fileadmin/user_upload/whitepaper.pdf',
     *          '/fileadmin/whitepaperProductX.pdf',
     *      ]
     *  ]
     *
     * @return array
     * @throws Exception
     * @throws InvalidQueryException
     */
    protected function getPageData(): array
    {
        $downloadRepository = ObjectUtility::getObjectManager()->get(DownloadRepository::class);
        $downloads = $downloadRepository->findCombinedByHref(
            ObjectUtility::getFilterDto(FilterDto::PERIOD_THISYEAR)
        );
        $titles = $amounts = [];
        $counter = 0;
        foreach ($downloads as $filename => $combinedDownloads) {
            $titles[] = $filename;
            $amounts[] = count($combinedDownloads);
            if ($counter >= 5) {
                break;
            }
        }
        return ['amounts' => $amounts, 'titles' => $titles];
    }
}
