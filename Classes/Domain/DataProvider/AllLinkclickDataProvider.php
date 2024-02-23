<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use DateTime;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AllLinkclickDataProvider extends AbstractDynamicFilterDataProvider
{
    protected ?LinkclickRepository $linkclickRepository = null;

    public function __construct(FilterDto $filter = null)
    {
        $this->linkclickRepository = GeneralUtility::makeInstance(LinkclickRepository::class);
        parent::__construct($filter);
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
     *      ],
     *      'max-y' => 100 // max value for logarithmic y-axes
     *  ]
     * @return void
     * @throws ExceptionDbal
     */
    public function prepareData(): void
    {
        $intervals = $this->filter->getIntervals();
        $frequency = $intervals['frequency'];
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
        $this->setMaxYValue();
        $this->overruleLatestTitle($frequency);
    }

    protected function setMaxYValue(): void
    {
        $maxValue = max(max($this->data['amounts']), max($this->data['amounts2']));
        $this->data['max-y'] = MathUtility::roundUp($maxValue);
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param string $pagelist
     * @return int
     * @throws ExceptionDbal
     */
    protected function getAmountOfPagevisitsInTimeframeAndPagelist(
        DateTime $start,
        DateTime $end,
        string $pagelist
    ): int {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select count(*) from ' . Pagevisit::TABLE_NAME
            . ' where crdate >= ' . $start->getTimestamp() . ' and crdate <= ' . $end->getTimestamp()
            . ' and deleted=0'
            . ' and site in ("' . implode('","', $this->filter->getSitesForFilter()) . '")';
        if ($pagelist !== '') {
            $sql .= ' and page in (' . $pagelist . ')';
        }
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    /**
     * Get all page identifiers with linkclicks within a timeframe
     *
     * @param array $intervals
     * @return string
     * @throws ExceptionDbal
     */
    protected function getRelatedPageListToLinkclicks(array $intervals): string
    {
        /** @var DateTime $start */
        $start = $intervals[0]['start'];
        /** @var DateTime $end */
        $end = end($intervals)['end'];
        $connection = DatabaseUtility::getConnectionForTable(Linkclick::TABLE_NAME);
        return (string)$connection->executeQuery(
            'select group_concat(distinct page) from ' . Linkclick::TABLE_NAME
            . ' where crdate >= ' . $start->getTimestamp() . ' and crdate <= ' . $end->getTimestamp()
            . ' and deleted=0'
            . ' and site in ("' . implode('","', $this->filter->getSitesForFilter()) . '")'
        )->fetchOne();
    }
}
