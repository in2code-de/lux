<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Utm;
use In2code\Lux\Utility\ArrayUtility;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class UtmRepository extends AbstractRepository
{
    /**
     * @param FilterDto $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByFilter(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForCrdate($filter, $query, []);
        $logicalAnd = $this->extendWithExtendedFilterQuery($query, $logicalAnd, $filter);
        $query->matching($query->logicalAnd(...$logicalAnd));
        return $query->execute();
    }

    /**
     * @return array
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function findAllCampaigns(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Utm::TABLE_NAME);
        $results = $queryBuilder
            ->select('utm_campaign')
            ->from(Utm::TABLE_NAME)
            ->where('utm_campaign != ""')
            ->groupBy('utm_campaign')
            ->executeQuery()
            ->fetchFirstColumn();
        return ArrayUtility::copyValuesToKeys($results);
    }

    /**
     * @return array
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function findAllSources(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Utm::TABLE_NAME);
        $results = $queryBuilder
            ->select('utm_source')
            ->from(Utm::TABLE_NAME)
            ->where('utm_source != ""')
            ->groupBy('utm_source')
            ->executeQuery()
            ->fetchFirstColumn();
        return ArrayUtility::copyValuesToKeys($results);
    }

    /**
     * @return array
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function findAllMedia(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Utm::TABLE_NAME);
        $results = $queryBuilder
            ->select('utm_medium')
            ->from(Utm::TABLE_NAME)
            ->where('utm_medium != ""')
            ->groupBy('utm_medium')
            ->executeQuery()
            ->fetchFirstColumn();
        return ArrayUtility::copyValuesToKeys($results);
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param FilterDto|null $filter
     * @return int
     * @throws InvalidQueryException
     */
    public function getNumberOfVisitorsInTimeFrame(DateTime $start, DateTime $end, FilterDto $filter = null): int
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
     * @param string $field "utm_campaign", "utm_source", "utm_medium"
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public function findCombinedByField(string $field, FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Utm::TABLE_NAME);
        $sql = 'select count(utm.' . $field . ') count, utm.' . $field . ' from ' . Utm::TABLE_NAME . ' utm'
            . ' where '
            . $this->extendWhereClauseWithFilterTime($filter, false, 'utm')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'utm', 'utm_source')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'utm', 'utm_medium', 'or')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'utm', 'utm_campaign', 'or')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'utm', 'utm_id', 'or')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'utm', 'utm_term', 'or')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'utm', 'utm_content', 'or')
            . $this->extendWhereClauseWithFilterCampaign($filter, 'utm')
            . $this->extendWhereClauseWithFilterSource($filter, 'utm')
            . $this->extendWhereClauseWithFilterMedium($filter, 'utm')
            . ' group by utm.' . $field . ' order by count desc limit 10';
        return $connection->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * @param FilterDto $filter
     * @param string $table
     * @return string
     */
    protected function extendWhereClauseWithFilterCampaign(FilterDto $filter, string $table = ''): string
    {
        $sql = '';
        if ($filter->getUtmCampaign() !== '') {
            $field = 'utm_campaign';
            if ($table !== '') {
                $field = $table . '.' . $field;
            }
            $sql .= ' and ' . $field . '="' . $filter->getUtmCampaign() . '"';
        }
        return $sql;
    }

    /**
     * @param FilterDto $filter
     * @param string $table
     * @return string
     */
    protected function extendWhereClauseWithFilterSource(FilterDto $filter, string $table = ''): string
    {
        $sql = '';
        if ($filter->getUtmSource() !== '') {
            $field = 'utm_source';
            if ($table !== '') {
                $field = $table . '.' . $field;
            }
            $sql .= ' and ' . $field . '="' . $filter->getUtmSource() . '"';
        }
        return $sql;
    }

    /**
     * @param FilterDto $filter
     * @param string $table
     * @return string
     */
    protected function extendWhereClauseWithFilterMedium(FilterDto $filter, string $table = ''): string
    {
        $sql = '';
        if ($filter->getUtmMedium() !== '') {
            $field = 'utm_medium';
            if ($table !== '') {
                $field = $table . '.' . $field;
            }
            $sql .= ' and ' . $field . '="' . $filter->getUtmMedium() . '"';
        }
        return $sql;
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
                    $logicalOr[] = $query->like('utmSource', '%' . $searchterm . '%');
                    $logicalOr[] = $query->like('utmMedium', '%' . $searchterm . '%');
                    $logicalOr[] = $query->like('utmCampaign', '%' . $searchterm . '%');
                    $logicalOr[] = $query->like('utmId', '%' . $searchterm . '%');
                    $logicalOr[] = $query->like('utmTerm', '%' . $searchterm . '%');
                    $logicalOr[] = $query->like('utmContent', '%' . $searchterm . '%');
                }
                $logicalAnd[] = $query->logicalOr(...$logicalOr);
            }
            if ($filter->getUtmCampaign() !== '') {
                $logicalAnd[] = $query->like('utmCampaign', '%' . $filter->getUtmCampaign() . '%');
            }
            if ($filter->getUtmMedium() !== '') {
                $logicalAnd[] = $query->like('utmMedium', '%' . $filter->getUtmMedium() . '%');
            }
            if ($filter->getUtmSource() !== '') {
                $logicalAnd[] = $query->like('utmSource', '%' . $filter->getUtmSource() . '%');
            }
        }
        return $logicalAnd;
    }
}
