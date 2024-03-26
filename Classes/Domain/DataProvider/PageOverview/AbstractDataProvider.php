<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider\PageOverview;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\DateUtility;

abstract class AbstractDataProvider
{
    /**
     * Don't show more results than this
     *
     * @var int
     */
    protected int $limit = 5;

    /**
     * @return array
     * @throws ExceptionDbal
     */
    protected function getPagevisitsOfGivenPage(): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select uid,visitor,crdate from ' . Pagevisit::TABLE_NAME
            . ' where page=' . (int)$this->filter->getSearchterm()
            . ' and crdate > ' . $this->filter->getStartTimeForFilter()->format('U')
            . ' and crdate < ' . $this->filter->getEndTimeForFilter()->format('U');
        return $connection->executeQuery($sql)->fetchAllAssociative();
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

    protected function cutResults(array $results): array
    {
        return array_slice($results, 0, $this->limit);
    }

    protected function getTimelimit(): int
    {
        return DateUtility::IS_INSAMEPAGEFUNNEL_TIME * 60;
    }
}
