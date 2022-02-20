<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\Referrer\Readable;
use In2code\Lux\Domain\Service\Referrer\SocialMedia;
use In2code\Lux\Utility\ArrayUtility;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\ExtensionUtility;
use In2code\Lux\Utility\FrontendUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     * Example result:
     *  [
     *      [
     *          'page' => Poge::class,
     *          'count' => 123
     *      ],
     *      [
     *          'page' => Poge::class,
     *          'count' => 124
     *      ]
     *  ]
     *
     * @param FilterDto $filter
     * @param int $limit
     * @return array
     * @throws ExceptionDbal
     * @throws Exception
     * @throws \Exception
     */
    public function findCombinedByPageIdentifier(FilterDto $filter, int $limit = 100): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select pv.page, count(pv.page) count, pv.domain from ' . Pagevisit::TABLE_NAME . ' pv'
            . ' left join ' . Page::TABLE_NAME . ' p on p.uid = pv.page'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = pv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where 1 '
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'p')
            . $this->extendWhereClauseWithFilterTime($filter, true, 'pv')
            . $this->extendWhereClauseWithFilterDomain($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by pv.page, pv.domain order by count desc limit ' . (int)$limit;
        $results = $connection->executeQuery($sql)->fetchAll();
        foreach ($results as &$result) {
            if ($result['page'] > 0) {
                /** @var PageRepository $pageRepository */
                $pageRepository = ObjectUtility::getObjectManager()->get(PageRepository::class);
                $page = $pageRepository->findRawByIdentifier($result['page']);
                if ($page !== []) {
                    $result['page'] = $page;
                }
            }
        }
        return $results;
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
        $logicalAnd = [
            $query->greaterThan('page.uid', 0)
        ];
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForCrdate($filter, $query, $logicalAnd);
        $query->matching(
            $query->logicalAnd($logicalAnd)
        );
        $query->setLimit(5);
        return $query->execute();
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param FilterDto|null $filter
     * @return int
     * @throws DBALException
     */
    public function getNumberOfVisitsInTimeFrame(\DateTime $start, \DateTime $end, FilterDto $filter = null): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select count(*) count from ' . Pagevisit::TABLE_NAME . ' pv'
            . $this->extendFromClauseWithJoinByFilter($filter, ['p', 'cs', 'v'])
            . ' where pv.crdate>=' . $start->getTimestamp() . ' and pv.crdate<=' . $end->getTimestamp()
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'p')
            . $this->extendWhereClauseWithFilterDomain($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs');
        return $connection->executeQuery($sql)->fetchColumn();
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
     * Get a result with pagevisits grouped by visitor
     *
     * @param Page $page
     * @param int $limit
     * @return array
     * @throws DBALException
     */
    public function findByPage(Page $page, int $limit = 100): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select uid,visitor,crdate from ' . Pagevisit::TABLE_NAME
            . ' where page=' . $page->getUid()
            . ' group by visitor,uid,crdate order by crdate desc limit ' . $limit;
        $pagevisitIdentifiers = $connection->executeQuery($sql)->fetchAll(\PDO::FETCH_COLUMN);
        return $this->convertIdentifiersToObjects($pagevisitIdentifiers, Pagevisit::TABLE_NAME);
    }

    /**
     * @param Visitor $visitor
     * @return \DateTime|null
     * @throws ExceptionDbal
     */
    public function findLatestDateByVisitor(Visitor $visitor): ?\DateTime
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select crdate from ' . Pagevisit::TABLE_NAME
            . ' where visitor=' . $visitor->getUid()
            . ' order by crdate desc limit 1';
        $timestamp = (int)$connection->executeQuery($sql)->fetchColumn();
        if ($timestamp > 0) {
            return \DateTime::createFromFormat('U', (string)$timestamp);
        }
        return null;
    }

    /**
     * @param Visitor $visitor
     * @param int $pageIdentifier
     * @return \DateTime|null
     * @throws ExceptionDbal
     */
    public function findLatestDateByVisitorAndPageIdentifier(Visitor $visitor, int $pageIdentifier): ?\DateTime
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select crdate from ' . Pagevisit::TABLE_NAME
            . ' where visitor=' . $visitor->getUid() . ' and page=' . (int)$pageIdentifier
            . ' order by crdate desc limit 1';
        $timestamp = (int)$connection->executeQuery($sql)->fetchColumn();
        if ($timestamp > 0) {
            return \DateTime::createFromFormat('U', (string)$timestamp);
        }
        return null;
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
     * @param int $pageIdentifier
     * @param Visitor $visitor
     * @return int
     * @throws ExceptionDbal
     */
    public function findAmountPerPageAndVisitor(int $pageIdentifier, Visitor $visitor): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        return (int)$connection->executeQuery(
            'select count(*) from ' . Pagevisit::TABLE_NAME
            . ' where page=' . $pageIdentifier . ' and visitor=' . $visitor->getUid()
        )->fetchColumn();
    }

    /**
     * Get an array with sorted values with a limit of 100 (but ignore current domain):
     * [
     *      'twitter.com' => 234,
     *      'facebook.com' => 123
     * ]
     *
     * @param FilterDto $filter
     * @param int $limit
     * @return array
     * @throws Exception
     * @throws ExceptionDbal
     */
    public function getAmountOfReferrers(FilterDto $filter, int $limit = 100): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select referrer, count(referrer) count from ' . Pagevisit::TABLE_NAME
            . ' where referrer != "" and referrer not like "%' . FrontendUtility::getCurrentDomain() . '%"'
            . $this->extendWhereClauseWithFilterTime($filter)
            . ' group by referrer having (count > 1) order by count desc limit ' . $limit;
        $records = (array)$connection->executeQuery($sql)->fetchAll();
        $result = [];
        foreach ($records as $record) {
            $readableReferrer = ObjectUtility::getObjectManager()->get(Readable::class, $record['referrer']);
            if (array_key_exists($readableReferrer->getReadableReferrer(), $result)) {
                $result[$readableReferrer->getReadableReferrer()] += $record['count'];
            } else {
                $result[$readableReferrer->getReadableReferrer()] = $record['count'];
            }
        }
        arsort($result);
        return $result;
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws Exception
     * @throws \Exception
     * @throws DBALException
     */
    public function getAmountOfSocialMediaReferrers(FilterDto $filter): array
    {
        $socialMedia = GeneralUtility::makeInstance(SocialMedia::class);
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $result = [];
        foreach ($socialMedia->getDomainsForQuery() as $name => $domains) {
            $sql = 'select count(*) count from ' . Pagevisit::TABLE_NAME . ' where referrer rlike "' . $domains . '"';
            $sql .= $this->extendWhereClauseWithFilterTime($filter, true);
            $count = (int)$connection->executeQuery($sql)->fetchColumn();
            if ($count > 0) {
                $result[$name] = $count;
            }
        }

        $result = $this->getAmountOfSocialMediaReferrersFromShorteners($result, $filter);

        arsort($result);
        return $result;
    }

    /**
     * Get social media amount of referrers from link shortener (part of luxenterprise)
     *
     * @param array $result
     * @param FilterDto $filter
     * @return array
     * @throws Exception
     */
    protected function getAmountOfSocialMediaReferrersFromShorteners(array $result, FilterDto $filter): array
    {
        if (ExtensionUtility::isLuxenterpriseVersionOrHigherAvailable('7.0.0')) {
            $shortenerRepository = ObjectUtility::getObjectManager()->get(
                \In2code\Luxenterprise\Domain\Repository\ShortenerRepository::class
            );
            $result2 = $shortenerRepository->findAmountsOfSocialMediaReferrers($filter, false);
            $result = ArrayUtility::sumAmountArrays($result, $result2);
        }
        return $result;
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws DBALException
     * @throws \Exception
     */
    public function getDomainsWithAmountOfVisits(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'SELECT count(*) as count, pv.domain FROM ' . Pagevisit::TABLE_NAME . ' pv'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = pv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where pv.domain!="" ' . $this->extendWhereClauseWithFilterTime($filter, true, 'pv')
            . $this->extendWhereClauseWithFilterDomain($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by domain order by count desc';
        return (array)$connection->executeQuery($sql)->fetchAll();
    }

    /**
     *  [
     *      'domain1.org',
     *      'www.domain2.org'
     *  ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws DBALException
     * @throws \Exception
     */
    public function getAllDomains(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'SELECT pv.domain FROM ' . Pagevisit::TABLE_NAME . ' pv'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = pv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where pv.domain!="" ' . $this->extendWhereClauseWithFilterTime($filter, true, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by domain order by domain asc';
        return (array)$connection->executeQuery($sql)->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws DBALException
     * @throws \Exception
     */
    public function getAllLanguages(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable('sys_language');
        $sql = 'SELECT count(*) as count, pv.language, l.title FROM ' . Pagevisit::TABLE_NAME . ' pv'
            . ' left join sys_language l on l.uid = pv.language'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = pv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where ' . $this->extendWhereClauseWithFilterTime($filter, false, 'pv')
            . $this->extendWhereClauseWithFilterDomain($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by pv.language, l.title order by count desc ';
        return (array)$connection->executeQuery($sql)->fetchAll();
    }

    /**
     * @param int $pageIdentifier
     * @param FilterDto $filter
     * @return int
     * @throws DBALException
     * @throws \Exception
     */
    public function findAbandonsForPage(int $pageIdentifier, FilterDto $filter): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $records = $connection->executeQuery(
            'select uid,visitor,crdate from ' . Pagevisit::TABLE_NAME . ' where page=' . $pageIdentifier
            . $this->extendWhereClauseWithFilterTime($filter)
        )->fetchAll();

        $abondons = 0;
        if ($records !== false) {
            foreach ($records as $record) {
                $result = $connection->executeQuery(
                    'select * from ' . Pagevisit::TABLE_NAME
                    . ' where visitor=' . (int)$record['visitor'] . ' and crdate>' . (int)$record['crdate']
                    . ' and crdate<' . ((int)$record['crdate'] + 300)
                )->fetchColumn();
                if ($result === false) {
                    $abondons++;
                }
            }
        }
        return $abondons;
    }

    /**
     * @param int $pageIdentifier
     * @param FilterDto $filter1
     * @param FilterDto $filter2
     * @return int positive if more visitors in filter1 period then in filter2, negative for the opposite situation
     * @throws DBALException
     */
    public function compareAmountPerPage(int $pageIdentifier, FilterDto $filter1, FilterDto $filter2): int
    {
        $amount1 = $this->findAmountPerPage($pageIdentifier, $filter1);
        $amount2 = $this->findAmountPerPage($pageIdentifier, $filter2);
        return $amount1 - $amount2;
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
                $logicalAnd[] = $query->equals('visitor.categoryscorings.category', $filter->getCategoryScoring());
            }
            if ($filter->getDomain() !== '') {
                $logicalAnd[] = $query->equals('domain', $filter->getDomain());
            }
        }
        return $logicalAnd;
    }
}
