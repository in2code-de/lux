<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class LinkclickRepository
 */
class LinkclickRepository extends AbstractRepository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING
    ];

    /**
     * @return int
     * @throws DBALException
     */
    public function findAllAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Linkclick::TABLE_NAME);
        return (int)$connection->executeQuery('select count(*) from ' . Linkclick::TABLE_NAME)->fetchColumn();
    }

    /**
     * @param int $linklistener
     * @return int
     */
    public function getFirstCreationDateFromLinklistenerIdentifier(int $linklistener): int
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Linkclick::TABLE_NAME);
        return (int)$queryBuilder
            ->select('crdate')
            ->from(Linkclick::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('linklistener', (int)$linklistener)
            )
            ->orderBy('crdate', 'asc')
            ->setMaxResults(1)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param int $linklistener
     * @return int
     */
    public function getLatestCreationDateFromLinklistenerIdentifier(int $linklistener): int
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Linkclick::TABLE_NAME);
        return (int)$queryBuilder
            ->select('crdate')
            ->from(Linkclick::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('linklistener', (int)$linklistener)
            )
            ->orderBy('crdate', 'desc')
            ->setMaxResults(1)
            ->execute()
            ->fetchColumn();
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
     * @throws DBALException
     * @throws \Exception
     */
    public function getAmountOfLinkclicksGroupedByPageUid(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Linkclick::TABLE_NAME);
        return (array)$connection->executeQuery(
            'select linklistener, count(linklistener) count, page from ' . Linkclick::TABLE_NAME
            . ' where ' . $this->extendWhereClauseWithFilterTime($filter, false) . ' group by linklistener, page'
        )->fetchAll();
    }

    /**
     * @param int $linklistener
     * @return array
     */
    public function findByLinklistenerIdentifier(int $linklistener): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Linkclick::TABLE_NAME);
        return (array)$queryBuilder
            ->select('*')
            ->from(Linkclick::TABLE_NAME)
            ->where('linklistener=' . (int)$linklistener)
            ->execute()
            ->fetchAll();
    }
}
