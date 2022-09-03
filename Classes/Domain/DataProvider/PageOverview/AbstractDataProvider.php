<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider\PageOverview;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\DateUtility;

/**
 * AbstractDataProvider
 */
abstract class AbstractDataProvider
{
    /**
     * Don't show more results then this
     *
     * @var int
     */
    protected $limit = 5;

    /**
     * @return array
     * @throws ExceptionDbal
     */
    protected function getPagevisitsOfGivenPage(): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $records = $connection->executeQuery(
            'select uid,visitor,crdate from ' . Pagevisit::TABLE_NAME
            . ' where page=' . (int)$this->filter->getSearchterm()
            . ' and crdate > ' . $this->filter->getStartTimeForFilter()->format('U')
            . ' and crdate < ' . $this->filter->getEndTimeForFilter()->format('U')
        )->fetchAll();
        if ($records === false) {
            return [];
        }
        return $records;
    }

    /**
     *  [
     *      [
     *          'amount' => 2
     *      ],
     *      [
     *          'amount' => 5
     *      ]
     *  ]
     *
     * to
     *
     *  [
     *      [
     *          'amount' => 5
     *      ],
     *      [
     *          'amount' => 2
     *      ]
     *  ]
     *
     * @param array $left
     * @param array $right
     * @return int
     */
    protected function sortByAmount(array $left, array $right): int
    {
        return ($left['amount'] < $right['amount']) ? 1 : (($left['amount'] > $right['amount']) ? -1 : 0);
    }

    /**
     * @param array $results
     * @return array
     */
    protected function cutResults(array $results): array
    {
        return array_slice($results, 0, $this->limit);
    }

    /**
     * @return int
     */
    protected function getTimelimit(): int
    {
        return DateUtility::IS_INSAMEPAGEFUNNEL_TIME * 60;
    }
}
