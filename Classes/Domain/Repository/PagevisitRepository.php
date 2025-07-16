<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use DateTime;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use Exception;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Company;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\Referrer\Readable;
use In2code\Lux\Domain\Service\Referrer\SocialMedia;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Exception\ArgumentsException;
use In2code\Lux\Utility\ArrayUtility;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\ExtensionUtility;
use In2code\Luxenterprise\Domain\Repository\ShortenerRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

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
     * @throws ExceptionDbalDriver
     */
    public function findCombinedByPageIdentifier(FilterDto $filter, int $limit = 100): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select pv.page, count(pv.page) count, max(pv.domain) as domain from ' . Pagevisit::TABLE_NAME . ' pv'
            . ' left join ' . Page::TABLE_NAME . ' p on p.uid = pv.page'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = pv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where 1 '
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'p')
            . $this->extendWhereClauseWithFilterTime($filter, true, 'pv')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by pv.page order by count desc limit ' . (int)$limit;
        $results = $connection->executeQuery($sql)->fetchAllAssociative();
        foreach ($results as &$result) {
            if ($result['page'] > 0) {
                $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
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
     * @throws Exception
     */
    public function findLatestPagevisits(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->greaterThan('page.uid', 0),
        ];
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForCrdate($filter, $query, $logicalAnd);
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForSite($filter, $query, $logicalAnd);
        $query->matching($query->logicalAnd(...$logicalAnd));
        $query->setLimit(5);
        return $query->execute();
    }

    public function findLatestPagevisitsWithCompanies(FilterDto $filter): QueryResultInterface
    {
        $sql = 'select c.uid companyuid, max(pv.uid) AS uid'
            . ' from ' . Pagevisit::TABLE_NAME . ' pv'
            . ' left join ' . Visitor::TABLE_NAME . ' v on pv.visitor = v.uid'
            . ' left join ' . Company::TABLE_NAME . ' c on v.companyrecord = c.uid'
            . ' where pv.deleted=0 and v.deleted=0 and c.deleted=0'
            . ' and v.blacklisted=0'
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'c')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . $this->extendWhereClauseWithFilterCountry($filter)
            . $this->extendWhereClauseWithFilterSizeClass($filter, 'c')
            . $this->extendWhereClauseWithFilterRevenueClass($filter, 'c')
            . $this->extendWhereClauseWithFilterBranchCode($filter)
            . $this->extendWhereClauseWithFilterCategory($filter, 'c')
            . ' group by c.uid, pv.crdate'
            . ' order by c.uid desc, pv.crdate desc'
            . ' limit ' . $filter->getLimit();
        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        $identifiers = ArrayUtility::convertFetchedAllArrayToNumericArray(
            $connection->executeQuery($sql)->fetchAllAssociative()
        );

        $query = $this->createQuery();
        $logicalAnd = [
            $query->in('uid', $identifiers ?: [0]),
        ];
        $query->matching(
            $query->logicalAnd(...$logicalAnd)
        );
        $query->setLimit($filter->getLimit());
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        return $query->execute();
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param FilterDto|null $filter
     * @return int
     * @throws ExceptionDbal
     */
    public function getNumberOfVisitsInTimeFrame(DateTime $start, DateTime $end, FilterDto $filter = null): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select count(*) count from ' . Pagevisit::TABLE_NAME . ' pv'
            . $this->extendFromClauseWithJoinByFilter($filter, ['p', 'cs', 'v'])
            . ' where pv.crdate>=' . $start->getTimestamp() . ' and pv.crdate<=' . $end->getTimestamp()
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'p')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterVisitor($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs');
        return $connection->executeQuery($sql)->fetchOne();
    }

    /**
     * Find all page visits of a visitor but with a given time. If a visitor visits our page every single day since
     * a week ago (so also today) and the given time is yesterday, we want to get all visits but not from today.
     *
     * @param Visitor $visitor
     * @param DateTime $time
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByVisitorAndTime(Visitor $visitor, DateTime $time): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->equals('visitor', $visitor),
            $query->lessThanOrEqual('crdate', $time),
        ];
        $query->matching($query->logicalAnd(...$logicalAnd));
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        return $query->execute();
    }

    /**
     * Find last page visit of a visitor but with a given time. If a visitor visits a page 3 days ago and today and
     * the given time is yesterday, we want to get the visit from 3 days ago
     *
     * @param Visitor $visitor
     * @param DateTime $time
     * @return Pagevisit|null
     * @throws InvalidQueryException
     */
    public function findLastByVisitorAndTime(Visitor $visitor, DateTime $time): ?Pagevisit
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->equals('visitor', $visitor),
            $query->lessThanOrEqual('crdate', $time),
        ];
        $query->matching($query->logicalAnd(...$logicalAnd));
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        /** @var Pagevisit $pagevisit */
        $pagevisit = $query->execute()->getFirst();
        return $pagevisit;
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws ArgumentsException
     * @throws ExceptionDbal
     */
    public function findByFilter(FilterDto $filter): array
    {
        if (MathUtility::canBeInterpretedAsInteger($filter->getSearchterm()) === false) {
            throw new ArgumentsException('Filter searchterm must keep a page identifier here', 1708775656);
        }

        $sql = 'select pv.uid,pv.visitor,pv.crdate,pv.page from ' . Pagevisit::TABLE_NAME . ' pv'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = pv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where pv.page=' . (int)$filter->getSearchterm() . ' '
            . $this->extendWhereClauseWithFilterTime($filter, true, 'pv')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by pv.visitor,pv.uid,pv.crdate,pv.page'
            . ' order by pv.crdate desc'
            . ' limit ' . ($filter->isLimitSet() ? $filter->getLimit() : 750);
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $pagevisitIdentifiers = $connection->executeQuery($sql)->fetchFirstColumn();
        return $this->convertIdentifiersToObjects($pagevisitIdentifiers, Pagevisit::TABLE_NAME);
    }

    /**
     * @param Visitor $visitor
     * @return DateTime|null
     * @throws ExceptionDbal
     */
    public function findLatestDateByVisitor(Visitor $visitor): ?DateTime
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select crdate from ' . Pagevisit::TABLE_NAME
            . ' where visitor=' . $visitor->getUid()
            . ' order by crdate desc limit 1';
        $timestamp = (int)$connection->executeQuery($sql)->fetchOne();
        if ($timestamp > 0) {
            return DateTime::createFromFormat('U', (string)$timestamp);
        }
        return null;
    }

    /**
     * @param Visitor $visitor
     * @param int $pageIdentifier
     * @return DateTime|null
     * @throws ExceptionDbal
     */
    public function findLatestDateByVisitorAndPageIdentifier(Visitor $visitor, int $pageIdentifier): ?DateTime
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select crdate from ' . Pagevisit::TABLE_NAME
            . ' where visitor=' . $visitor->getUid() . ' and page=' . $pageIdentifier
            . ' order by crdate desc limit 1';
        $timestamp = (int)$connection->executeQuery($sql)->fetchOne();
        if ($timestamp > 0) {
            return DateTime::createFromFormat('U', (string)$timestamp);
        }
        return null;
    }

    /**
     * @return int
     * @throws ExceptionDbal
     */
    public function findAllAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        return (int)$connection->executeQuery('select count(*) from ' . Pagevisit::TABLE_NAME)->fetchOne();
    }

    /**
     * @param int $pageIdentifier
     * @param FilterDto $filter
     * @return int
     * @throws Exception
     */
    public function findAmountPerPage(int $pageIdentifier, FilterDto $filter): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        return (int)$connection->executeQuery(
            'select count(*) from ' . Pagevisit::TABLE_NAME . ' where page=' . $pageIdentifier
            . $this->extendWhereClauseWithFilterTime($filter)
        )->fetchOne();
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
        )->fetchOne();
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
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public function getAmountOfReferrers(FilterDto $filter, int $limit = 100): array
    {
        $siteService = GeneralUtility::makeInstance(SiteService::class);
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'select referrer, count(referrer) count from ' . Pagevisit::TABLE_NAME
            . ' where referrer != \'\''
            . ' and referrer not regexp "' . $siteService->getAllDomainsForWhereClause() . '"'
            . $this->extendWhereClauseWithFilterTime($filter)
            . $this->extendWhereClauseWithFilterSite($filter)
            . ' group by referrer having (count > 1) order by count desc limit ' . $limit;
        $records = $connection->executeQuery($sql)->fetchAllAssociative();
        $result = [];
        foreach ($records as $record) {
            $readableReferrer = GeneralUtility::makeInstance(Readable::class, $record['referrer']);
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
     * @throws ExceptionDbal
     */
    public function getAmountOfSocialMediaReferrers(FilterDto $filter): array
    {
        $socialMedia = GeneralUtility::makeInstance(SocialMedia::class);
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $result = [];
        foreach ($socialMedia->getDomainsForQuery() as $name => $domains) {
            $sql = 'select count(*) count from ' . Pagevisit::TABLE_NAME . ' where referrer rlike "' . $domains . '"';
            $sql .= $this->extendWhereClauseWithFilterTime($filter);
            $sql .= $this->extendWhereClauseWithFilterSite($filter);
            $count = (int)$connection->executeQuery($sql)->fetchOne();
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
     */
    protected function getAmountOfSocialMediaReferrersFromShorteners(array $result, FilterDto $filter): array
    {
        if (ExtensionUtility::isLuxenterpriseVersionOrHigherAvailable('7.0.0')) {
            $shortenerRepository = GeneralUtility::makeInstance(ShortenerRepository::class);
            $result2 = $shortenerRepository->findAmountsOfSocialMediaReferrers($filter, false);
            $result = ArrayUtility::sumAmountArrays($result, $result2);
        }
        return $result;
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws Exception
     */
    public function getDomainsWithAmountOfVisits(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'SELECT count(*) as count, pv.domain FROM ' . Pagevisit::TABLE_NAME . ' pv'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = pv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where pv.domain!="" ' . $this->extendWhereClauseWithFilterTime($filter, true, 'pv')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by domain order by count desc';
        return (array)$connection->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     *  [
     *      'domain1.org',
     *      'www.domain2.org'
     *  ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws Exception
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
        return $connection->executeQuery($sql)->fetchFirstColumn();
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws Exception
     */
    public function getAllLanguages(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'SELECT count(*) as count, pv.language FROM ' . Pagevisit::TABLE_NAME . ' pv'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = pv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' where ' . $this->extendWhereClauseWithFilterTime($filter, false, 'pv')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' group by pv.language order by count desc ';
        return $connection->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * @param int $pageIdentifier
     * @param FilterDto $filter
     * @return int
     * @throws Exception
     */
    public function findAbandonsForPage(int $pageIdentifier, FilterDto $filter): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $records = $connection->executeQuery(
            'select uid,visitor,crdate from ' . Pagevisit::TABLE_NAME . ' where page=' . $pageIdentifier
            . $this->extendWhereClauseWithFilterTime($filter)
        )->fetchAllAssociative();

        $abondons = 0;
        if ($records !== false) {
            foreach ($records as $record) {
                $result = $connection->executeQuery(
                    'select * from ' . Pagevisit::TABLE_NAME
                    . ' where visitor=' . (int)$record['visitor'] . ' and crdate>' . (int)$record['crdate']
                    . ' and crdate<' . ((int)$record['crdate'] + 300)
                )->fetchOne();
                if ($result === false) {
                    $abondons++;
                }
            }
        }
        return $abondons;
    }

    public function findFirstForCompany(Company $company): ?Pagevisit
    {
        return $this->findOneByCompany($company);
    }

    public function findLatestForCompany(Company $company): ?Pagevisit
    {
        return $this->findOneByCompany($company, 'desc');
    }

    protected function findOneByCompany(Company $company, string $orderings = 'asc'): ?Pagevisit
    {
        $sql = 'select pv.uid'
            . ' from ' . Company::TABLE_NAME . ' c'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.companyrecord = c.uid'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on pv.visitor = v.uid'
            . ' where c.uid=' . $company->getUid() . ' and c.deleted=0 and v.deleted=0'
            . ' and v.blacklisted=0 and pv.deleted=0'
            . ' order by pv.crdate ' . $orderings
            . ' limit 1';
        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        $identifier = $connection->executeQuery($sql)->fetchOne();
        return $this->findByUid($identifier);
    }

    /**
     * @param int $pageIdentifier
     * @param FilterDto $filter1
     * @param FilterDto $filter2
     * @return int positive if more visitors in filter1 period then in filter2, negative for the opposite situation
     * @throws Exception
     */
    public function compareAmountPerPage(int $pageIdentifier, FilterDto $filter1, FilterDto $filter2): int
    {
        $amount1 = $this->findAmountPerPage($pageIdentifier, $filter1);
        $amount2 = $this->findAmountPerPage($pageIdentifier, $filter2);
        return $amount1 - $amount2;
    }
}
