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
     * @param string $tag
     * @return int
     */
    public function getFirstCreationDateFromTagName(string $tag): int
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Linkclick::TABLE_NAME);
        return (int)$queryBuilder
            ->select('crdate')
            ->from(Linkclick::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('tag', $queryBuilder->createNamedParameter($tag))
            )
            ->orderBy('crdate', 'asc')
            ->setMaxResults(1)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param string $tag
     * @return int
     */
    public function getLatestCreationDateFromTagName(string $tag): int
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Linkclick::TABLE_NAME);
        return (int)$queryBuilder
            ->select('crdate')
            ->from(Linkclick::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('tag', $queryBuilder->createNamedParameter($tag))
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
     *          'tag' => 'Tagname Foo',
     *          'count' => 5,
     *          'page' => 123
     *      ],
     *      [
     *          'tag' => 'Tagname Foo', // same tag as above but with different pageUid
     *          'count' => 3,
     *          'page' => 222
     *      ],
     *      [
     *          'tag' => 'Tagname Bar',
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
            'select tag, count(tag) count, page from ' . Linkclick::TABLE_NAME
            . ' where ' . $this->extendWhereClauseWithFilterTime($filter, false) . ' group by tag, page'
        )->fetchAll();
    }
}
