<?php
/** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class DownloadRepository
 */
class DownloadRepository extends AbstractRepository
{
    /**
     * @param string $href
     * @param int $limit
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByHref(string $href, int $limit = 100): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->equals('href', $href),
            $query->greaterThan('visitor.uid', 0),
        ];
        $query->matching($query->logicalAnd($logicalAnd));
        $query->setLimit($limit);
        return $query->execute();
    }

    /**
     * Find all combined by href ordered by number of downloads with a limit of 100
     *
     * @param FilterDto $filter
     * @return array
     * @throws InvalidQueryException
     * @throws \Exception
     */
    public function findCombinedByHref(FilterDto $filter): array
    {
        $query = $this->createQuery();
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForCrdate($filter, $query, []);
        $logicalAnd = $this->extendWithExtendedFilterQuery($query, $logicalAnd, $filter);
        $query->matching($query->logicalAnd($logicalAnd));
        $assets = $query->execute(true);

        $result = [];
        /** @var Download $asset */
        foreach ($assets as $asset) {
            $result[$asset['href']][] = $asset;
        }
        array_multisort(array_map('count', $result), SORT_DESC, $result);
        $result = array_slice($result, 0, 100);
        return $result;
    }

    /**
     * Find all downloads of a visitor but with a given time. If a visitor would download an asset every single day
     * since a week ago (so also today) and the given time is yesterday, we want to get all downloads but not from
     * today.
     *
     * @param Visitor $visitor
     * @param \DateTime $time
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByVisitorAndTime(Visitor $visitor, \DateTime $time): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->equals('visitor', $visitor),
            $query->lessThanOrEqual('crdate', $time)
        ];
        $query->matching($query->logicalAnd($logicalAnd));
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        return $query->execute();
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param FilterDto|null $filter
     * @return int
     * @throws InvalidQueryException
     */
    public function getNumberOfDownloadsInTimeFrame(\DateTime $start, \DateTime $end, FilterDto $filter = null): int
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->greaterThanOrEqual('crdate', $start->format('U')),
            $query->lessThanOrEqual('crdate', $end->format('U'))
        ];
        $logicalAnd = $this->extendWithExtendedFilterQuery($query, $logicalAnd, $filter);
        $query->matching($query->logicalAnd($logicalAnd));
        return (int)$query->execute()->count();
    }

    /**
     * @return int
     * @throws DBALException
     */
    public function findAllAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Download::TABLE_NAME);
        return (int)$connection->executeQuery('select count(uid) from ' . Download::TABLE_NAME)->fetchColumn();
    }

    /**
     * @param FilterDto $filter
     * @param QueryInterface $query
     * @param array $logicalAnd
     * @return array
     * @throws InvalidQueryException
     */
    protected function extendWithExtendedFilterQuery(
        QueryInterface $query,
        array $logicalAnd,
        FilterDto $filter = null
    ): array {
        if ($filter !== null) {
            if ($filter->getSearchterm() !== '') {
                $logicalOr = [];
                foreach ($filter->getSearchterms() as $searchterm) {
                    if (MathUtility::canBeInterpretedAsInteger($searchterm)) {
                        $logicalOr[] = $query->equals('file.uid', (int)$searchterm);
                    } else {
                        $logicalOr[] = $query->like('file.name', '%' . $searchterm . '%');
                    }
                }
                $logicalAnd[] = $query->logicalOr($logicalOr);
            }
            if ($filter->getScoring() > 0) {
                $logicalAnd[] = $query->greaterThanOrEqual('visitor.scoring', $filter->getScoring());
            }
            if ($filter->getCategoryScoring() !== null) {
                $logicalAnd[] = $query->equals('visitor.categoryscorings.category', $filter->getCategoryScoring());
            }
        }
        return $logicalAnd;
    }
}
