<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use DateTime;
use Doctrine\DBAL\Exception as ExceptionDbal;
use Exception;
use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\DateUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class LinkclickRepository extends AbstractRepository
{
    /**
     * @return int
     * @throws ExceptionDbal
     */
    public function findAllAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Linkclick::TABLE_NAME);
        return (int)$connection->executeQuery('select count(*) from ' . Linkclick::TABLE_NAME)->fetchOne();
    }

    /**
     * @param int $linklistener
     * @return int
     * @throws ExceptionDbal
     */
    public function getFirstCreationDateFromLinklistenerIdentifier(int $linklistener): int
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Linkclick::TABLE_NAME);
        return (int)$queryBuilder
            ->select('crdate')
            ->from(Linkclick::TABLE_NAME)
            ->where($queryBuilder->expr()->eq('linklistener', (int)$linklistener))
            ->orderBy('crdate', 'asc')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * @param int $linklistener
     * @return int
     * @throws ExceptionDbal
     */
    public function getLatestCreationDateFromLinklistenerIdentifier(int $linklistener): int
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Linkclick::TABLE_NAME);
        return (int)$queryBuilder
            ->select('crdate')
            ->from(Linkclick::TABLE_NAME)
            ->where($queryBuilder->expr()->eq('linklistener', (int)$linklistener))
            ->orderBy('crdate', 'desc')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * Example result values:
     *  [
     *      [
     *          'linklistener' => 1,
     *          'count' => 5,
     *          'page' => 123
     *      ],
     *      [
     *          'linklistener' => 1, // same tag as above but with different pageUid
     *          'count' => 3,
     *          'page' => 222
     *      ],
     *      [
     *          'linklistener' => 2,
     *          'count' => 34,
     *          'page' => 1
     *      ],
     *  ]
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function getAmountOfLinkclicksGroupedByPageUid(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Linkclick::TABLE_NAME);
        $sql = 'select lc.linklistener, count(lc.linklistener) count, lc.page'
            . ' from ' . Linkclick::TABLE_NAME . ' lc'
            . ' left join ' . Linklistener::TABLE_NAME . ' ll on lc.linklistener=ll.uid'
            . ' where ' . $this->extendWhereClauseWithFilterTime($filter, false, 'lc');
        $sql .= $this->extendWhereClauseWithFilterSearchterms($filter, 'll');
        $sql .= $this->extendWhereClauseWithFilterCategoryScoring($filter, 'll');
        $sql .= $this->extendWhereClauseWithFilterSite($filter, 'lc');
        $sql .= ' group by lc.linklistener, lc.page';
        return $connection->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * Example result values:
     *  [
     *      [
     *          'clickcount' => 5,
     *          'page' => 123,
     *          'crdate' => 123456544 // first click on this linklistener on page 123
     *      ],
     *      [
     *          'clickcount' => 3,
     *          'page' => 222,
     *          'crdate' => 543224555
     *      ]
     *  ]
     * @param int $linklistener
     * @return array
     * @throws Exception
     */
    public function getAmountOfLinkclicksByLinklistenerGroupedByPageUid(int $linklistener): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Linkclick::TABLE_NAME);
        return (array)$connection->executeQuery(
            'select count(linklistener) clickcount, page, crdate from ' . Linkclick::TABLE_NAME
            . ' where linklistener=' . (int)$linklistener . ' group by page, crdate order by crdate asc'
        )->fetchAllAssociative();
    }

    /**
     * @param int $pageIdentifier
     * @param FilterDto $filter
     * @return int
     * @throws ExceptionDbal
     * @throws Exception
     */
    public function getAmountOfLinkclicksByPageIdentifierAndTimeframe(int $pageIdentifier, FilterDto $filter): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Linkclick::TABLE_NAME);
        $sql = 'select count(*) count'
            . ' from ' . Linkclick::TABLE_NAME
            . ' where page=' . (int)$pageIdentifier
            . $this->extendWhereClauseWithFilterTime($filter);
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    /**
     * @param int $linklistener
     * @param int $page
     * @return DateTime
     * @throws Exception
     */
    public function findLastDateByLinklistenerAndPage(int $linklistener, int $page): DateTime
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Linkclick::TABLE_NAME);
        $date = (int)$queryBuilder
            ->select('crdate')
            ->from(Linkclick::TABLE_NAME)
            ->where('linklistener=' . (int)$linklistener . ' and page=' . (int)$page)
            ->orderBy('crdate', 'desc')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();
        if ($date > 0) {
            return DateUtility::convertTimestamp($date);
        }
        return new DateTime();
    }

    /**
     * @param FilterDto $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByFilter(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->equals('linklistener', (int)$filter->getSearchterm()),
        ];
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForCrdate($filter, $query, $logicalAnd);
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForSite($filter, $query, $logicalAnd);
        $query->matching($query->logicalAnd(...$logicalAnd));
        $query->setLimit($filter->getLimit());
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        return $query->execute();
    }

    /**
     * @param int $linklistener
     * @return array
     * @throws ExceptionDbal
     */
    public function findRawByLinklistenerIdentifier(int $linklistener): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Linkclick::TABLE_NAME);
        return $queryBuilder
            ->select('*')
            ->from(Linkclick::TABLE_NAME)
            ->where('linklistener=' . (int)$linklistener)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param FilterDto|null $filter
     * @return int
     * @throws ExceptionDbal
     */
    public function findByTimeFrame(DateTime $start, DateTime $end, FilterDto $filter = null): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Linkclick::TABLE_NAME);
        $sql = 'select count(*) count'
            . ' from ' . Linkclick::TABLE_NAME . ' lc'
            . ' left join ' . Linklistener::TABLE_NAME . ' ll on lc.linklistener = ll.uid'
            . ' where lc.crdate >= ' . $start->getTimestamp() . ' and lc.crdate <= ' . $end->getTimestamp();
        $sql .= $this->extendWhereClauseWithFilterSearchterms($filter, 'll');
        $sql .= $this->extendWhereClauseWithFilterCategoryScoring($filter, 'll');
        $sql .= $this->extendWhereClauseWithFilterSite($filter, 'lc');
        return (int)$connection->executeQuery($sql)->fetchOne();
    }
}
