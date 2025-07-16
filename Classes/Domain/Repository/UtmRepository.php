<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use DateTime;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Pagevisit;
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
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForIsReferrer($filter, $query, $logicalAnd);
        $query->matching($query->logicalAnd(...$logicalAnd));
        $query->setLimit(750);
        return $query->execute();
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function findAllCampaigns(FilterDto $filter): array
    {
        return $this->findAllProperties('utm_campaign', $filter);
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function findAllSources(FilterDto $filter): array
    {
        return $this->findAllProperties('utm_source', $filter);
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function findAllMedia(FilterDto $filter): array
    {
        return $this->findAllProperties('utm_medium', $filter);
    }

    /**
     * @param string $property
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    protected function findAllProperties(string $property, FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Utm::TABLE_NAME);
        $sql = 'select utm.' . $property;
        $sql .= ' from ' . Utm::TABLE_NAME . ' utm';
        $sql .= ' left join ' . Pagevisit::TABLE_NAME . ' pv on pv.uid = utm.pagevisit';
        $sql .= ' left join ' . Newsvisit::TABLE_NAME . ' nv on nv.uid = utm.newsvisit';
        $sql .= ' left join ' . Pagevisit::TABLE_NAME . ' pnv on pnv.uid = nv.pagevisit';
        $sql .= ' where utm.' . $property . ' != \'\'';
        $sql .= ' and (pv.site in ("' . implode('","', $filter->getSitesForFilter()) . '") or';
        $sql .= ' pnv.site in ("' . implode('","', $filter->getSitesForFilter()) . '"))';
        $sql .= $this->extendWhereClauseWithFilterTime($filter, true, 'utm');
        $sql .= ' group by utm.' . $property;
        $sql .= ' order by utm.' . $property . ' asc';
        $sql .= ' limit 1000';
        $results = $connection->executeQuery($sql)->fetchFirstColumn();
        return ArrayUtility::copyValuesToKeys($results);
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param FilterDto|null $filter
     * @return int
     * @throws InvalidQueryException
     */
    public function getNumberOfVisitorsInTimeFrame(DateTime $start, DateTime $end, ?FilterDto $filter = null): int
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->greaterThanOrEqual('crdate', $start->format('U')),
            $query->lessThanOrEqual('crdate', $end->format('U')),
        ];
        $logicalAnd = $this->extendWithExtendedFilterQuery($query, $logicalAnd, $filter);
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForIsReferrer($filter, $query, $logicalAnd);
        $query->matching($query->logicalAnd(...$logicalAnd));
        return $query->execute()->count();
    }

    /**
     * @param string $field "utm_campaign", "utm_source", "utm_medium"
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function findCombinedByField(string $field, FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Utm::TABLE_NAME);
        $sql = 'select count(utm.' . $field . ') count, utm.' . $field
            . ' from ' . Utm::TABLE_NAME . ' utm'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on pv.uid = utm.pagevisit'
            . ' left join ' . Newsvisit::TABLE_NAME . ' nv on nv.uid = utm.newsvisit'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pnv on pnv.uid = nv.pagevisit'
            . ' where ' . $field . ' != \'\''
            . $this->extendWhereClauseWithFilterTime($filter, true, 'utm')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'utm', 'utm_source')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'utm', 'utm_medium', 'or')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'utm', 'utm_campaign', 'or')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'utm', 'utm_id', 'or')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'utm', 'utm_term', 'or')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'utm', 'utm_content', 'or')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'utm', 'referrer', 'or')
            . $this->extendWhereClauseWithFilterCampaign($filter, 'utm')
            . $this->extendWhereClauseWithFilterSource($filter, 'utm')
            . $this->extendWhereClauseWithFilterMedium($filter, 'utm')
            . $this->extendWhereClauseWithFilterSite($filter)
            . $this->extendWhereClauseWithFilterIsReferrer($filter, 'utm')
            . ' group by utm.' . $field . ' order by count desc limit 8';
        return $connection->executeQuery($sql)->fetchAllAssociative();
    }

    protected function extendWhereClauseWithFilterCampaign(FilterDto $filter, string $table = ''): string
    {
        $sql = '';
        if ($filter->isUtmCampaignSet()) {
            $field = 'utm_campaign';
            if ($table !== '') {
                $field = $table . '.' . $field;
            }
            $sql .= ' and ' . $field . '="' . $filter->getUtmCampaign() . '"';
        }
        return $sql;
    }

    protected function extendWhereClauseWithFilterSource(FilterDto $filter, string $table = ''): string
    {
        $sql = '';
        if ($filter->isUtmSourceSet()) {
            $field = 'utm_source';
            if ($table !== '') {
                $field = $table . '.' . $field;
            }
            $sql .= ' and ' . $field . '="' . $filter->getUtmSource() . '"';
        }
        return $sql;
    }

    protected function extendWhereClauseWithFilterMedium(FilterDto $filter, string $table = ''): string
    {
        $sql = '';
        if ($filter->isUtmMediumSet()) {
            $field = 'utm_medium';
            if ($table !== '') {
                $field = $table . '.' . $field;
            }
            $sql .= ' and ' . $field . '="' . $filter->getUtmMedium() . '"';
        }
        return $sql;
    }

    /**
     * Returns part of a where clause like
     *      ' and site="site 1"'
     *
     * @param FilterDto $filter
     * @param string $table table with crdate (normally the main table)
     * @return string
     */
    protected function extendWhereClauseWithFilterSite(FilterDto $filter, string $table = ''): string
    {
        $sql = ' and (';
        $sql .= 'pv.site in ("' . implode('","', $filter->getSitesForFilter()) . '")';
        $sql .= ' or ';
        $sql .= 'pnv.site in ("' . implode('","', $filter->getSitesForFilter()) . '")';
        $sql .= ')';
        return $sql;
    }

    protected function extendWhereClauseWithFilterIsReferrer(FilterDto $filter, string $table = ''): string
    {
        $sql = '';
        if ($filter->isWithReferrerSet()) {
            $field = 'referrer';
            if ($table !== '') {
                $field = $table . '.' . $field;
            }
            $sql .= ' and ' . $field . ' != \'\'';
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
        ?FilterDto $filter = null
    ): array {
        if ($filter !== null) {
            if ($filter->isSearchtermSet()) {
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
            if ($filter->isUtmCampaignSet()) {
                $logicalAnd[] = $query->like('utmCampaign', '%' . $filter->getUtmCampaign() . '%');
            }
            if ($filter->isUtmMediumSet()) {
                $logicalAnd[] = $query->like('utmMedium', '%' . $filter->getUtmMedium() . '%');
            }
            if ($filter->isUtmSourceSet()) {
                $logicalAnd[] = $query->like('utmSource', '%' . $filter->getUtmSource() . '%');
            }
            if ($filter->isUtmContentSet()) {
                $logicalAnd[] = $query->like('utmContent', '%' . $filter->getUtmContent() . '%');
            }
            $logicalAnd[] = $query->logicalOr(
                $query->in('pagevisit.site', $filter->getSitesForFilter()),
                $query->in('newsvisit.pagevisit.site', $filter->getSitesForFilter())
            );
        }
        return $logicalAnd;
    }

    protected function extendLogicalAndWithFilterConstraintsForIsReferrer(
        FilterDto $filter,
        QueryInterface $query,
        array $logicalAnd
    ): array {
        if ($filter->isWithReferrerSet()) {
            $logicalAnd[] = $query->logicalNot($query->equals('referrer', ''));
        }
        return $logicalAnd;
    }
}
