<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\ReadableReferrerService;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\FrontendUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class PagevisitRepository
 */
class PagevisitRepository extends AbstractRepository
{
    /**
     * Find by single page entries with all pagevisits ordered by number of pagevisits with a limit of 100
     *
     * @param FilterDto $filter
     * @return array
     * @throws InvalidQueryException
     * @throws \Exception
     */
    public function findCombinedByPageIdentifier(FilterDto $filter): array
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->greaterThan('crdate', $filter->getStartTimeForFilter()),
            $query->lessThan('crdate', $filter->getEndTimeForFilter()),
            $query->greaterThan('page.uid', 0),
        ];
        $logicalAnd = $this->extendWithExtendedFilterQuery($query, $logicalAnd, $filter);
        $query->matching($query->logicalAnd($logicalAnd));
        $pages = $query->execute(true);
        return $this->combineAndCutPages($pages);
    }

    /**
     * @param FilterDto $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     * @throws \Exception
     */
    public function findLatestPagevisits(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd([
                $query->greaterThan('crdate', $filter->getStartTimeForFilter()),
                $query->lessThan('crdate', $filter->getEndTimeForFilter()),
                $query->greaterThan('page.uid', 0)
            ])
        );
        $query->setLimit(5);
        return $query->execute();
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param FilterDto $filter
     * @return int
     * @throws InvalidQueryException
     */
    public function getNumberOfVisitorsInTimeFrame(\DateTime $start, \DateTime $end, FilterDto $filter = null): int
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
     * Find all page visits of a visitor but with a given time. If a visitor visits our page every single day since
     * a week ago (so also today) and the given time is yesterday, we want to get all visits but not from today.
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
     * Find last page visit of a visitor but with a given time. If a visitor visits a page 3 days ago and today and
     * the given time is yesterday, we want to get the visit from 3 days ago
     *
     * @param Visitor $visitor
     * @param \DateTime $time
     * @return Pagevisit|null
     * @throws InvalidQueryException
     */
    public function findLastByVisitorAndTime(Visitor $visitor, \DateTime $time)
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->equals('visitor', $visitor),
            $query->lessThanOrEqual('crdate', $time)
        ];
        $query->matching($query->logicalAnd($logicalAnd));
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        /** @var Pagevisit $pagevisit */
        $pagevisit = $query->execute()->getFirst();
        return $pagevisit;
    }

    /**
     * @param Page $page
     * @param int $limit
     * @return array
     */
    public function findByPage(Page $page, int $limit = 100): array
    {
        $query = $this->createQuery();
        $query->matching($query->equals('page', $page));
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        $query->setLimit($limit * 100);
        $pagesvisits = $query->execute();

        $result = [];
        /** @var Pagevisit $pagevisit */
        foreach ($pagesvisits as $pagevisit) {
            if (array_key_exists($pagevisit->getVisitor()->getUid(), $result) === false) {
                $result[$pagevisit->getVisitor()->getUid()] = $pagevisit;
            }
        }
        $result = array_slice($result, 0, $limit);
        return $result;
    }

    /**
     * @return int
     * @throws DBALException
     */
    public function findAllAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        return (int)$connection->executeQuery('select count(*) from ' . Pagevisit::TABLE_NAME)->fetchColumn();
    }

    /**
     * @param int $pageIdentifier
     * @param FilterDto $filter
     * @return int
     * @throws DBALException
     * @throws \Exception
     */
    public function findAmountPerPage(int $pageIdentifier, FilterDto $filter): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        return (int)$connection->executeQuery(
            'select count(*) from ' . Pagevisit::TABLE_NAME . ' where page=' . $pageIdentifier
            . $this->extendWhereClauseWithFilterTime($filter)
        )->fetchColumn();
    }

    /**
     * @param $pages
     * @return array
     */
    protected function combineAndCutPages($pages): array
    {
        $result = [];
        foreach ($pages as $pageProperties) {
            $result[$pageProperties['page']][] = $pageProperties;
        }
        array_multisort(array_map('count', $result), SORT_DESC, $result);
        $result = array_slice($result, 0, 100);
        return $result;
    }

    /**
     * Get an array with sorted values with a limit of 100 (but ignore current domain):
     * [
     *      'twitter.com' => 234,
     *      'facebook.com' => 123
     * ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws DBALException
     * @throws Exception
     * @throws \Exception
     */
    public function getAmountOfReferrers(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select referrer, count(referrer) count from ' . Pagevisit::TABLE_NAME
            . ' where referrer != "" and referrer not like "%' . FrontendUtility::getCurrentDomain() . '%"'
            . $this->extendWhereClauseWithFilterTime($filter)
            . ' group by referrer having (count > 1) order by count desc limit 100';
        $records = (array)$connection->executeQuery($sql)->fetchAll();
        $result = [];
        foreach ($records as $record) {
            $readableReferrer = ObjectUtility::getObjectManager()->get(
                ReadableReferrerService::class,
                $record['referrer']
            );
            if (array_key_exists($readableReferrer->getReadableReferrer(), $result)) {
                $result[$readableReferrer->getReadableReferrer()] += $record['count'];
            } else {
                $result[$readableReferrer->getReadableReferrer()] = $record['count'];
            }
        }
        return $result;
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws DBALException
     */
    public function getAllDomains(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'SELECT count(*) as count, pv.domain FROM ' . Pagevisit::TABLE_NAME . ' pv'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = pv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where pv.domain!="" ' . $this->extendWhereClauseWithFilterTime($filter, true, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by domain order by count desc';
        return (array)$connection->executeQuery($sql)->fetchAll();
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws DBALException
     */
    public function getAllLanguages(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable('sys_language');
        $sql = 'SELECT count(*) as count, pv.language, l.title FROM ' . Pagevisit::TABLE_NAME . ' pv'
            . ' left join sys_language l on l.uid = pv.language'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = pv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where ' . $this->extendWhereClauseWithFilterTime($filter, false, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by pv.language order by count desc ';
        return (array)$connection->executeQuery($sql)->fetchAll();
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
                        $logicalOr[] = $query->equals('page.uid', (int)$searchterm);
                    } else {
                        $logicalOr[] = $query->like('page.title', '%' . $searchterm . '%');
                    }
                }
                $logicalAnd[] = $query->logicalOr($logicalOr);
            }
            if ($filter->getScoring() > 0) {
                $logicalAnd[] = $query->greaterThanOrEqual('visitor.scoring', $filter->getScoring());
            }
            if ($filter->getCategoryScoring() !== null) {
                $logicalAnd[] = $query->contains('visitor.categoryscorings', $filter->getCategoryScoring());
            }
        }
        return $logicalAnd;
    }
}
