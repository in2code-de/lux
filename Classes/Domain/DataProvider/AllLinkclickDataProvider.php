<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Utility\DatabaseUtility;
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
     *      'amounts' => [ // Link clicks
     *          34,
     *          8,
     *          23
     *      ],
     *      'amounts2' => [ // Pagevisits
     *          33%,
     *          98%,
     *          1%
     *      ]
     *  ]
     * @return void
     * @throws \Exception
     */
    public function prepareData(): void
    {
        $intervals = $this->filter->getIntervals();
        $frequency = (string)$intervals['frequency'];
        $pageList = $this->getRelatedPageListToLinkclicks($intervals['intervals']);
        foreach ($intervals['intervals'] as $interval) {
            $clicks = $this->linkclickRepository->findByTimeFrame(
                $interval['start'],
                $interval['end'],
                $this->filter
            );
            $this->data['amounts'][] = $clicks;
            $this->data['amounts2'][] = $this->getAmountOfPagevisitsInTimeframeAndPagelist(
                $interval['start'],
                $interval['end'],
                $pageList
            );
            $this->data['titles'][] = $this->getLabelForFrequency($frequency, $interval['start']);
        }
        $this->overruleLatestTitle($frequency);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param string $pagelist
     * @return int
     * @throws DBALException
     */
    protected function getAmountOfPagevisitsInTimeframeAndPagelist(
        \DateTime $start,
        \DateTime $end,
        string $pagelist
    ): int {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        return (int)$connection->executeQuery(
            'select count(*) from ' . Pagevisit::TABLE_NAME
            . ' where crdate >= ' . $start->getTimestamp() . ' and crdate <= ' . $end->getTimestamp()
            . ' and page in (' . $pagelist . ') and deleted=0'
        )->fetchColumn();
    }

    /**
     * Get all page identifiers with linkclicks within a timeframe
     *
     * @param array $intervals
     * @return string
     * @throws DBALException
     */
    protected function getRelatedPageListToLinkclicks(array $intervals): string
    {
        /** @var \DateTime $start */
        $start = $intervals[0]['start'];
        /** @var \DateTime $end */
        $end = end($intervals)['end'];
        $connection = DatabaseUtility::getConnectionForTable(Linkclick::TABLE_NAME);
        return (string)$connection->executeQuery(
            'select group_concat(distinct page) from ' . Linkclick::TABLE_NAME
            . ' where crdate >= ' . $start->getTimestamp() . ' and crdate <= ' . $end->getTimestamp() . ' and deleted=0'
        )->fetchColumn();
    }
}
