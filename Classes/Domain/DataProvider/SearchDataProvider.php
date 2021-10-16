<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\DataProvider;

use Exception;
use In2code\Lux\Domain\Repository\SearchRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * SearchDataProvider
 */
class SearchDataProvider extends AbstractDynamicFilterDataProvider
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
     *          'TYPO3',
     *          'Marketing',
     *          'Automation'
     *      ]
     *  ]
     *
     * @return void
     * @throws Exception
     */
    public function prepareData(): void
    {
        $searchRepository = GeneralUtility::makeInstance(SearchRepository::class);
        $intervals = $this->filter->getIntervals();
        $frequency = (string)$intervals['frequency'];
        foreach ($intervals['intervals'] as $interval) {
            $this->data['amounts'][] = $searchRepository->getNumberOfSearchUsersInTimeFrame(
                $interval['start'],
                $interval['end'],
                $this->filter
            );
            $this->data['titles'][] = $this->getLabelForFrequency($frequency, $interval['start']);
        }
        $this->overruleLatestTitle($frequency);
    }
}
