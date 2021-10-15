<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\DataProvider;

use In2code\Lux\Domain\Repository\DownloadRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DownloadsDataProvider
 */
class DownloadsDataProvider extends AbstractDynamicFilterDataProvider
{
    /**
     * Set values like:
     *  [
     *      'amounts' => [
     *          50,
     *          88,
     *          33
     *      ],
     *      'titles' => [
     *          'Th',
     *          'Fr',
     *          'Now'
     *      ]
     *  ]
     *
     * @return void
     * @throws \Exception
     */
    public function prepareData(): void
    {
        $downloadRepository = GeneralUtility::makeInstance(DownloadRepository::class);
        $intervals = $this->filter->getIntervals();
        $frequency = (string)$intervals['frequency'];
        foreach ($intervals['intervals'] as $interval) {
            $this->data['amounts'][] = $downloadRepository->getNumberOfDownloadsInTimeFrame(
                $interval['start'],
                $interval['end'],
                $this->filter
            );
            $this->data['titles'][] = $this->getLabelForFrequency($frequency, $interval['start']);
        }
        $this->overruleLatestTitle($frequency);
    }
}
