<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use DateTime;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\News;
use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Exception\ArgumentsException;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class NewsvisitRepository extends AbstractRepository
{
    /**
     * newsIdentifier => count
     *  [
     *      ['count' => 12, 'news' => {News Object 1}]
     *      ['count' => 55, 'news' => {News Object 2}]
     *  ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function findCombinedByNewsIdentifier(FilterDto $filter): array
    {
        if (ExtensionManagementUtility::isLoaded('news') === false) {
            return [];
        }

        $connection = DatabaseUtility::getConnectionForTable(Newsvisit::TABLE_NAME);
        $sql = 'select count(distinct nv.uid) count, nv.news from ' . Newsvisit::TABLE_NAME . ' nv'
            . ' left join ' . News::TABLE_NAME . ' n on nv.news = n.uid'
            . ' left join ' . Visitor::TABLE_NAME . ' v on nv.visitor = v.uid'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on cs.visitor = v.uid'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on pv.uid = nv.pagevisit'
            . ' where '
            . $this->extendWhereClauseWithFilterTime($filter, false, 'nv')
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'n')
            . $this->extendWhereClauseWithFilterDomain($filter, 'pv')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by nv.news order by count desc';
        $rows = $connection->executeQuery($sql)->fetchAllAssociative();
        return $this->combineAndCutNews($rows);
    }

    protected function combineAndCutNews(array $rows): array
    {
        $newsRepository = GeneralUtility::makeInstance(NewsRepository::class);
        $objects = [];
        foreach ($rows as $row) {
            $news = $newsRepository->findByIdentifier($row['news']);
            if ($news !== null) {
                $objects[] = [
                    'count' => $row['count'],
                    'news' => $news,
                ];
            }
        }
        return $objects;
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
        $query->matching($query->logicalAnd(...$logicalAnd));
        return $query->execute()->count();
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function getDomainsWithAmountOfVisits(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Newsvisit::TABLE_NAME);
        $sql = 'SELECT count(distinct nv.uid) as count, pv.domain FROM ' . Newsvisit::TABLE_NAME . ' nv'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on pv.uid = nv.pagevisit'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = nv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where '
            . $this->extendWhereClauseWithFilterTime($filter, false, 'nv')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by domain order by count desc';
        return $connection->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * Get a result with pagevisits grouped by visitor
     *
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     * @throws ArgumentsException
     */
    public function findByFilter(FilterDto $filter): array
    {
        if (MathUtility::canBeInterpretedAsInteger($filter->getSearchterm()) === false) {
            throw new ArgumentsException('Filter searchterm must keep a news identifier here', 1708792874);
        }

        $connection = DatabaseUtility::getConnectionForTable(Newsvisit::TABLE_NAME);
        $sql = 'select nv.uid,nv.visitor,nv.crdate from ' . Newsvisit::TABLE_NAME . ' nv'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on nv.pagevisit = pv.uid'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = pv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where nv.news=' . (int)$filter->getSearchterm()
            . $this->extendWhereClauseWithFilterTime($filter, true, 'nv')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by nv.visitor,nv.uid,nv.crdate'
            . ' order by crdate desc'
            . ' limit ' . $filter->getLimit();
        $newsvisitIdentifiers = $connection->executeQuery($sql)->fetchFirstColumn();
        return $this->convertIdentifiersToObjects($newsvisitIdentifiers, Newsvisit::TABLE_NAME);
    }

    public function findByPagevisit(Pagevisit $pagevisit): ?Newsvisit
    {
        $query = $this->createQuery();
        $query->matching($query->equals('pagevisit', $pagevisit->getUid()));
        $query->setLimit(1);
        $query->setOrderings(['uid' => QueryInterface::ORDER_DESCENDING]);
        /** @var Newsvisit $newsvisit */
        $newsvisit = $query->execute()->getFirst();
        return $newsvisit;
    }

    /**
     *  [
     *      'domain1.org',
     *      'www.domain2.org'
     *  ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function getAllDomains(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Newsvisit::TABLE_NAME);
        $sql = 'SELECT pv.domain FROM ' . Newsvisit::TABLE_NAME . ' nv'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on pv.uid = nv.pagevisit'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = nv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where pv.domain!="" '
            . $this->extendWhereClauseWithFilterTime($filter, true, 'nv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by domain order by domain asc';
        return $connection->executeQuery($sql)->fetchFirstColumn();
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function getAllLanguages(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Newsvisit::TABLE_NAME);
        $sql = 'SELECT count(distinct nv.uid) as count, nv.language FROM ' . Newsvisit::TABLE_NAME . ' nv'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on pv.uid = nv.pagevisit'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = nv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where '
            . $this->extendWhereClauseWithFilterTime($filter, false, 'nv')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by nv.language order by count desc';
        return $connection->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * @return bool
     * @throws ExceptionDbal
     */
    public function isTableFilled(): bool
    {
        if (DatabaseUtility::isTableExisting(News::TABLE_NAME)) {
            $connection = DatabaseUtility::getConnectionForTable(News::TABLE_NAME);
            $sql = 'select count(*) from ' . News::TABLE_NAME . ' where deleted=0';
            return $connection->executeQuery($sql)->fetchOne() > 0;
        }
        return false;
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
                    if (MathUtility::canBeInterpretedAsInteger($searchterm)) {
                        $logicalOr[] = $query->equals('news.uid', (int)$searchterm);
                    } else {
                        $logicalOr[] = $query->like('news.title', '%' . $searchterm . '%');
                    }
                }
                $logicalAnd[] = $query->logicalOr(...$logicalOr);
            }
            if ($filter->isScoringSet()) {
                $logicalAnd[] = $query->greaterThanOrEqual('visitor.scoring', $filter->getScoring());
            }
            if ($filter->isCategoryScoringSet()) {
                $logicalAnd[] = $query->equals('visitor.categoryscorings.category', $filter->getCategoryScoring());
            }
            if ($filter->isDomainSet()) {
                $logicalAnd[] = $query->equals('pagevisit.domain', $filter->getDomain());
            }
            $logicalAnd[] = $query->in('pagevisit.site', $filter->getSitesForFilter());
        }
        return $logicalAnd;
    }
}
