<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use In2code\Lux\Domain\Repository\NewsvisitRepository;
use In2code\Lux\Utility\ObjectUtility;

/**
 * Class NewsvisistsDataProvider
 */
class NewsvisistsDataProvider extends AbstractDynamicFilterDataProvider
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
        $newsvisitRepository = ObjectUtility::getObjectManager()->get(NewsvisitRepository::class);
        $intervals = $this->filter->getIntervals();
        $frequency = (string)$intervals['frequency'];
        foreach ($intervals['intervals'] as $interval) {
            $this->data['amounts'][] = $newsvisitRepository->getNumberOfVisitorsInTimeFrame(
                $interval['start'],
                $interval['end'],
                $this->filter
            );
            $this->data['titles'][] = $this->getLabelForFrequency($frequency, $interval['start']);
        }
        $this->overruleLatestTitle($frequency);
    }
}
