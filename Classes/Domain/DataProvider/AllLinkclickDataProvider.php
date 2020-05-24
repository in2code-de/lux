<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class AllLinkclickDataProvider
 */
class AllLinkclickDataProvider extends AbstractDynamicFilterDataProvider
{
    /**
     * @var LinkclickRepository
     */
    protected $linkclickRepository = null;

    /**
     * LinkclickDataProvider constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->linkclickRepository = ObjectUtility::getObjectManager()->get(LinkclickRepository::class);
        parent::__construct();
    }

    /**
     * Set values like
     *  [
     *      'titles' => [
     *          'Mo',
     *          'Tu',
     *          'We'
     *      ],
     *      'amounts' => [
     *          34,
     *          8,
     *          23
     *      ]
     *  ]
     * @return void
     * @throws \Exception
     */
    public function prepareData(): void
    {
        $intervals = $this->filter->getIntervals();
        $frequency = (string)$intervals['frequency'];
        foreach ($intervals['intervals'] as $interval) {
            $this->data['amounts'][] = $this->linkclickRepository->findByTimeFrame(
                $interval['start'],
                $interval['end'],
                $this->filter
            );
            $this->data['titles'][] = $this->getLabelForFrequency($frequency, $interval['start']);
        }
        $this->overruleLatestTitle($frequency);
    }
}
