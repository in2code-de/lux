<?php
declare(strict_types = 1);
namespace In2code\Lux\Widgets\DataProvider;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\DownloadRepository;
use In2code\Lux\Utility\FileUtility;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class LuxDownloadsDataProvider
 * @noinspection PhpUnused
 */
class LuxDownloadsDataProvider implements ChartDataProviderInterface
{
    /**
     * @return array
     * @throws Exception
     * @throws InvalidQueryException
     */
    public function getChartData(): array
    {
        $data = $this->getDownloadData();
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
    protected function getDownloadData(): array
    {
        $downloadRepository = ObjectUtility::getObjectManager()->get(DownloadRepository::class);
        $downloads = $downloadRepository->findCombinedByHref(
            ObjectUtility::getFilterDto(FilterDto::PERIOD_THISYEAR)
        );
        $titles = $amounts = [];
        $counter = 0;
        foreach ($downloads as $filename => $combinedDownloads) {
            if ($counter > 5) {
                break;
            }
            $counter++;
            $title = StringUtility::cropString(FileUtility::getFilenameFromPathAndFilename($filename), 40);
            if ((int)$combinedDownloads[0]['file'] > 0) {
                $title .= ' (id=' . $combinedDownloads[0]['file'] . ')';
            }
            $titles[] = $title;
            $amounts[] = count($combinedDownloads);
        }
        return ['amounts' => $amounts, 'titles' => $titles];
    }
}
