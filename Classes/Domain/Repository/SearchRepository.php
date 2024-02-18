<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use DateTime;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Search;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class SearchRepository extends AbstractRepository
{
    /**
     * newsIdentifier => count
     *  [
     *      ['count' => 55, 'searchterm' => 'lux'],
     *      ['count' => 12, 'searchterm' => 'luxletter']
     *  ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws Exception
     * @throws ExceptionDbalDriver
     */
    public function findCombinedBySearchIdentifier(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Search::TABLE_NAME);
        $sql = 'select count(*) count, searchterm from ' . Search::TABLE_NAME . ' s'
            . ' left join ' . Visitor::TABLE_NAME . ' v on s.visitor = v.uid'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on cs.visitor = v.uid'
            . ' where '
            . $this->extendWhereClauseWithFilterTime($filter, false, 's')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 's', 'searchterm')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by searchterm order by count desc';
        return $connection->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param FilterDto|null $filter
     * @return int
     * @throws InvalidQueryException
     */
    public function getNumberOfSearchUsersInTimeFrame(DateTime $start, DateTime $end, FilterDto $filter = null): int
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->greaterThanOrEqual('crdate', $start->format('U')),
            $query->lessThanOrEqual('crdate', $end->format('U')),
        ];
        $logicalAnd = $this->extendWithExtendedFilterQuery($query, $logicalAnd, $filter);
        $query->matching($query->logicalAnd(...$logicalAnd));
        return $query->execute()->count();
    }

    /**
     * @param QueryInterface $query
     * @param array $logicalAnd
     * @param FilterDto|null $filter
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
                    $logicalOr[] = $query->like('searchterm', '%' . $searchterm . '%');
                }
                $logicalAnd[] = $query->logicalOr(...$logicalOr);
            }
            if ($filter->getScoring() > 0) {
                $logicalAnd[] = $query->greaterThanOrEqual('visitor.scoring', $filter->getScoring());
            }
            if ($filter->getCategoryScoring() !== null) {
                $logicalAnd[] = $query->equals('visitor.categoryscorings.category', $filter->getCategoryScoring());
            }
            if ($filter->getDomain() !== '') {
                $logicalAnd[] = $query->equals('pagevisit.domain', $filter->getDomain());
            }
        }
        return $logicalAnd;
    }
}
