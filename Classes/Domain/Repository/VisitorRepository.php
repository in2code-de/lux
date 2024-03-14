<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use DateTime;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use Exception;
use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Company;
use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Ipinformation;
use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Search;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Utm;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Exception\ParametersException;
use In2code\Lux\Utility\ArrayUtility;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\DateUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class VisitorRepository extends AbstractRepository
{
    /**
     * Find a visitor by its fingerprint and deliver also blacklisted visitors
     *
     * @param string $identificator
     * @param int $type
     * @return Visitor|null
     */
    public function findOneAndAlsoBlacklistedByFingerprint(string $identificator, int $type): ?Visitor
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $and = [
            $query->equals('deleted', false),
            $query->equals('fingerprints.value', $identificator),
            $query->equals('fingerprints.type', $type),
        ];
        $query->matching($query->logicalAnd(...$and));
        $query->setOrderings(['crdate' => QueryInterface::ORDER_ASCENDING]);
        $query->setLimit(1);
        /** @var Visitor $visitor */
        $visitor = $query->execute()->getFirst();
        return $visitor;
    }

    /**
     * @param FilterDto $filter
     * @return array ->toArray() improves performance up to 100% on some cases
     * @throws InvalidQueryException
     */
    public function findAllWithIdentifiedFirst(FilterDto $filter): array
    {
        // Search for single visitor by calculated hash
        if ($filter->isSearchtermSet() && StringUtility::isShortMd5($filter->getSearchterm())) {
            $visitor = $this->findByHash($filter);
            if ($visitor !== null) {
                return [$visitor];
            }
        }

        $query = $this->createQuery();
        $logicalAnd = $this->extendLogicalAndWithFilterConstraints($filter, $query, []);
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForSite($filter, $query, $logicalAnd, 'pagevisits');
        $query->matching($query->logicalAnd(...$logicalAnd));
        $query->setOrderings($this->getOrderingsArrayByFilterDto($filter));
        $query->setLimit($filter->getLimit());
        return $query->execute()->toArray();
    }

    public function findAllWithIdentifiedFirstAmount(FilterDto $filter): int
    {
        $sql = 'select count(distinct v.uid) from ' . Visitor::TABLE_NAME . ' v'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on v.uid = pv.visitor'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' left join ' . Attribute::TABLE_NAME . ' a on v.uid = a.visitor'
            . ' where v.deleted=0 and v.blacklisted=0'
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'v')
            . $this->extendWhereClauseWithFilterTime($filter, true, 'pv')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . $this->extendWhereClauseWithFilterIdentified($filter)
            . $this->extendWhereClauseWithFilterPid($filter)
            . ' limit 1';
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws InvalidQueryException
     */
    public function findAllWithKnownCompanies(FilterDto $filter): array
    {
        $visitors = $this->findAllWithIdentifiedFirst($filter);
        $withCompanies = [];
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            if ($visitor->getCompany() !== '') {
                $withCompanies[] = $visitor;
            }
        }
        return $withCompanies;
    }

    /**
     * Is used for a command and the API requests in luxenterprise
     *
     * @param string $propertyName
     * @param string $propertyValue
     * @param bool $exactMatch
     * @param array $orderings
     * @param int $limit
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findAllByProperty(
        string $propertyName,
        string $propertyValue,
        bool $exactMatch,
        array $orderings = ['uid' => 'DESC'],
        int $limit = 1000
    ): QueryResultInterface {
        $query = $this->createQuery();
        $constraint = $query->equals(StringUtility::cleanString($propertyName), $propertyValue);
        if ($exactMatch === false) {
            $constraint = $query->like(StringUtility::cleanString($propertyName), '%' . $propertyValue . '%');
        }
        $query->matching($constraint);
        $query->setOrderings(ArrayUtility::cleanStringForArrayKeys($orderings));
        $query->setLimit($limit);
        return $query->execute();
    }

    /**
     * Used in API of luxenterprise
     *
     * @param array $properties [['name' => 'email', 'value' => '@in2code', 'operator' => 'equals']]
     * @param array $orderings
     * @param int $limit
     * @return QueryResultInterface
     * @throws ParametersException
     * @noinspection PhpUnused
     */
    public function findAllByAnyProperties(array $properties, array $orderings, int $limit): QueryResultInterface
    {
        $allowedOperators = ['greaterThan', 'greaterThanOrEqual', 'lessThan', 'lessThanOrEqual', 'equals', 'like'];
        $query = $this->createQuery();
        $and = [];
        foreach ($properties as $property) {
            if (empty($property['operator']) || in_array($property['operator'], $allowedOperators) === false) {
                throw new ParametersException('Given property operator is empty or not valid', 1650987242);
            }
            if (empty($property['name']) || empty($property['value'])) {
                throw new ParametersException('Property name or value is empty', 1650988934);
            }
            $and[] = $query->{$property['operator']}(
                StringUtility::cleanString($property['name'], false, '.%_-'),
                $property['value']
            );
        }
        $query->matching($query->logicalAnd(...$and));
        $query->setOrderings(ArrayUtility::cleanStringForArrayKeys($orderings));
        $query->setLimit($limit);
        return $query->execute();
    }

    /**
     * Used in API of luxenterprise
     *
     * @param string $propertyName
     * @param string $propertyValue
     * @return Visitor|null
     * @noinspection PhpUnused
     */
    public function findByProperty(string $propertyName, string $propertyValue): ?Visitor
    {
        $query = $this->createQuery();
        $query->matching($query->equals(StringUtility::cleanString($propertyName, false, '.%_-'), $propertyValue));
        /** @var Visitor $visitor */
        $visitor = $query->execute()->getFirst();
        return $visitor;
    }

    /**
     * Find a small couple of the hottest visitors
     *
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function findByHottestScorings(FilterDto $filter)
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        $sql = 'select distinct v.uid, v.scoring, v.tstamp from ' . Visitor::TABLE_NAME . ' v'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on v.uid = pv.visitor'
            . ' left join ' . Page::TABLE_NAME . ' p on p.uid = pv.page'
            . ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor'
            . ' left join ' . Attribute::TABLE_NAME . ' a on v.uid = a.visitor'
            . ' where v.deleted=0 and v.hidden=0 and v.identified=1'
            . $this->extendWhereClauseWithFilterSearchterms($filter, 'v', 'email')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . $this->extendWhereClauseWithFilterScoring($filter, 'v')
            . $this->extendWhereClauseWithFilterCategoryScoring($filter, 'cs')
            . ' order by v.scoring DESC, v.tstamp DESC'
            . ' limit ' . $filter->getLimit();
        $rows = $connection->executeQuery($sql)->fetchAllAssociative();
        $results = [];
        foreach ($rows as $row) {
            $results[] = $this->findByUid($row['uid']);
        }
        return $results;
    }

    /**
     * Find duplicates (also blacklisted records) by given email
     *
     * @param string $email
     * @return QueryResultInterface
     */
    public function findDuplicatesByEmail(string $email): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->matching($query->logicalAnd($query->equals('deleted', false), $query->equals('email', $email)));
        $query->setOrderings(['crdate' => QueryInterface::ORDER_ASCENDING]);
        return $query->execute();
    }

    /**
     * @param string $fingerprint
     * @return QueryResultInterface
     */
    public function findDuplicatesByFingerprint(string $fingerprint): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->equals('fingerprints.value', $fingerprint),
            $query->equals('fingerprints.type', Fingerprint::TYPE_FINGERPRINT),
        ];
        $query->matching($query->logicalAnd(...$logicalAnd));
        $query->setOrderings(['crdate' => QueryInterface::ORDER_ASCENDING]);
        return $query->execute();
    }

    /**
     * Show the last three visitors of a visited page
     *
     * @param int $pageIdentifier
     * @return QueryResultInterface
     */
    public function findByVisitedPageIdentifier(int $pageIdentifier): QueryResultInterface
    {
        /**
         * Normal extbase query building failed on some db server because of extbase bug:
         * https://forge.typo3.org/issues/94899
         */
        $query = $this->createQuery();
        $sql = 'select v.*, pv.tstamp';
        $sql .= ' from ' . Visitor::TABLE_NAME . ' v left join ' . Pagevisit::TABLE_NAME . ' pv on v.uid=pv.visitor';
        $sql .= ' where pv.page=' . (int)$pageIdentifier . ' and v.deleted=0 and pv.deleted=0';
        $sql .= ' order by pv.tstamp DESC limit 3';
        return $query->statement($sql)->execute();
    }

    /**
     * @param FilterDto $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByUniqueSiteVisits(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [$query->equals('visits', 1)];
        $logicalAnd = $this->extendLogicalAndWithFilterConstraints($filter, $query, $logicalAnd);
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForSite($filter, $query, $logicalAnd, 'pagevisits');
        $query->matching($query->logicalAnd(...$logicalAnd));
        return $query->execute();
    }

    /**
     * @param FilterDto $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByRecurringSiteVisits(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [$query->greaterThan('visits', 1)];
        $logicalAnd = $this->extendLogicalAndWithFilterConstraints($filter, $query, $logicalAnd);
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForSite($filter, $query, $logicalAnd, 'pagevisits');
        $query->matching($query->logicalAnd(...$logicalAnd));
        return $query->execute();
    }

    /**
     * @param FilterDto $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findIdentified(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [$query->equals('identified', true)];
        $logicalAnd = $this->extendLogicalAndWithFilterConstraints($filter, $query, $logicalAnd);
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForSite($filter, $query, $logicalAnd, 'pagevisits');
        $query->matching($query->logicalAnd(...$logicalAnd));
        return $query->execute();
    }

    /**
     * @param FilterDto $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findUnknown(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [$query->equals('identified', false)];
        $logicalAnd = $this->extendLogicalAndWithFilterConstraints($filter, $query, $logicalAnd);
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForSite($filter, $query, $logicalAnd, 'pagevisits');
        $query->matching($query->logicalAnd(...$logicalAnd));
        return $query->execute();
    }

    /**
     * Find visitors that are now on the website (5 Min last activity)
     *
     * @param FilterDto $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     * @throws Exception
     */
    public function findOnline(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [$query->greaterThan('pagevisits.tstamp', DateUtility::getCurrentOnlineDateTime())];
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForSite($filter, $query, $logicalAnd, 'pagevisits');
        $query->matching($query->logicalAnd(...$logicalAnd));
        $query->setLimit($filter->getLimit());
        $query->setOrderings(['tstamp' => QueryInterface::ORDER_DESCENDING]);
        return $query->execute();
    }

    /**
     * Find visitors where tstamp is older than given timestamp
     *
     * @param int $timestamp
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByLastChange(int $timestamp): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->lessThan('tstamp', $timestamp));
        return $query->execute();
    }

    /**
     * Find unknown visitors where tstamp is older than given timestamp
     *
     * @param int $timestamp
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByLastChangeUnknown(int $timestamp): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->equals('identified', false),
            $query->lessThan('tstamp', $timestamp),
        ];
        $query->matching($query->logicalAnd(...$logicalAnd));
        return $query->execute();
    }

    /**
     * @param string $email
     * @return array like [1,3,5]
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public function findByEmailAndEmptyFrontenduser(string $email): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Visitor::TABLE_NAME);
        return $queryBuilder
            ->select('uid')
            ->from(Visitor::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('email', $queryBuilder->createNamedParameter($email)),
                $queryBuilder->expr()->eq('frontenduser', 0)
            )
            ->executeQuery()
            ->fetchFirstColumn();
    }

    /**
     * @return bool
     * @throws ExceptionDbal
     */
    public function isVisitorExistingWithDefaultLanguage(): bool
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        return (int)$connection->executeQuery(
            'select count(*) from ' . Visitor::TABLE_NAME . ' where sys_language_uid > -1'
        )->fetchOne() > 0;
    }

    /**
     * @return int
     * @throws ExceptionDbal
     */
    public function findAllAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        return (int)$connection->executeQuery('select count(uid) from ' . Visitor::TABLE_NAME)->fetchOne();
    }

    /**
     * @return int
     * @throws ExceptionDbal
     */
    public function findAllIdentifiedAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        return (int)$connection->executeQuery('select count(uid) from ' . Visitor::TABLE_NAME . ' where identified = 1')
            ->fetchOne();
    }

    /**
     * @return int
     * @throws ExceptionDbal
     */
    public function findAllUnknownAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        return (int)$connection->executeQuery('select count(uid) from ' . Visitor::TABLE_NAME . ' where identified = 0')
            ->fetchOne();
    }

    public function findByHash(FilterDto $filter): ?Visitor
    {
        if (StringUtility::isShortMd5($filter->getSearchterm()) === false) {
            return null;
        }

        $sql = 'select v.uid from ' . Visitor::TABLE_NAME . ' v'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on pv.visitor=v.uid'
            . ' where SUBSTR(MD5(v.uid), 1, 6) = "' . $filter->getSearchterm() . '"'
            . ' and v.deleted=0'
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . ' limit 1';
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        $identifier = $connection->executeQuery($sql)->fetchOne() ?: 0;

        if ($identifier > 0) {
            $query = $this->createQuery();
            $query->matching($query->equals('uid', $identifier));
            return $query->execute()->getFirst();
        }

        return null;
    }

    /**
     * Example result:
     *  [
     *      123 => '123.456.0.1', // visitor uid => ip address
     *      2456 => '127.0.0.1',
     *  ]
     *
     * @param int $limit get X latest records
     * @param DateTime $time
     * @param bool $noCompaniesOnly get only visitors without company relation
     * @return array
     * @throws ExceptionDbal
     */
    public function findLatestVisitorsWithIpAddress(int $limit, DateTime $time, bool $noCompaniesOnly = true): array
    {
        $sql = 'select uid,ip_address from ' . Visitor::TABLE_NAME
            . ' where ip_address not like "%***" and ip_address != \'\' and crdate >= ' . $time->getTimestamp();
        if ($noCompaniesOnly === true) {
            $sql .= ' and companyrecord = 0';
        }
        $sql .= ' order by uid desc limit ' . $limit;
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        return $connection->executeQuery($sql)->fetchAllKeyValue();
    }

    public function findAmountOfVisitorsInTimeFrame(DateTime $start, DateTime $end, FilterDto $filter): int
    {
        $sql = 'select count(distinct v.uid) from ' . Visitor::TABLE_NAME . ' v'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on v.uid = pv.visitor'
            . ' where v.deleted=0 and v.blacklisted=0'
            . ' and pv.crdate >= ' . $start->getTimestamp() . ' and pv.crdate <= ' . $end->getTimestamp()
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . ' limit 1';
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    public function findAmountOfExistingVisitorsInTimeFrame(DateTime $start, DateTime $end, FilterDto $filter): int
    {
        $newVisitors = $this->findAmountOfNewVisitorsInTimeFrame($start, $end, $filter);
        $allVisitors = $this->findAmountOfVisitorsInTimeFrame($start, $end, $filter);
        return $allVisitors - $newVisitors;
    }

    public function findAmountOfNewVisitorsInTimeFrame(DateTime $start, DateTime $end, FilterDto $filter): int
    {
        $sql = 'select count(distinct v.uid) from ' . Visitor::TABLE_NAME . ' v'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on v.uid = pv.visitor'
            . ' where v.deleted=0 and v.blacklisted=0'
            . ' and v.crdate >= ' . $start->getTimestamp() . ' and v.crdate <= ' . $end->getTimestamp()
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . ' limit 1';
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    public function getScoringSumFromCompany(Company $company): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        $sql = 'select sum(scoring) scoring from ' . Visitor::TABLE_NAME
            . ' where deleted=0 and blacklisted=0 and companyrecord = ' . $company->getUid();
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    public function findByCompany(Company $company, int $limit = 200): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        $sql = 'select v.uid,v.scoring from ' . Visitor::TABLE_NAME . ' v'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on v.uid = pv.visitor'
            . ' where v.deleted=0 and v.blacklisted=0 and v.companyrecord = ' . $company->getUid()
            . $this->extendWhereClauseWithFilterSite(ObjectUtility::getFilterDto(), 'pv')
            . ' group by v.uid,v.scoring'
            . ' order by v.identified desc, v.scoring desc'
            . ' limit ' . $limit;
        $results = $connection->executeQuery($sql)->fetchAllAssociative();

        $visitors = [];
        foreach ($results as $result) {
            $visitor = $this->findByUid($result['uid']);
            if ($visitor !== null) {
                $visitors[] = $visitor;
            }
        }
        return $visitors;
    }

    public function canVisitorBeReadBySites(Visitor $visitor, array $sites): bool
    {
        $sql = 'select v.uid from ' . Visitor::TABLE_NAME . ' v'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on v.uid = pv.visitor'
            . ' where v.deleted=0 and v.blacklisted=0 and v.uid=' . $visitor->getUid()
            . ' and pv.site in ("' . implode('","', $sites) . '")'
            . ' limit 1';
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        return (int)$connection->executeQuery($sql)->fetchOne() > 0;
    }

    /**
     * @param int $visitorIdentifier
     * @param int $frontenduserIdentifier
     * @return void
     * @throws ExceptionDbal
     */
    public function updateVisitorWithFrontendUserRelation(int $visitorIdentifier, int $frontenduserIdentifier): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        $connection->executeQuery(
            'update ' . Visitor::TABLE_NAME . ' set frontenduser=' . (int)$frontenduserIdentifier
            . ' where uid=' . $visitorIdentifier
        );
    }

    /**
     * @return void
     * @throws ExceptionDbal
     */
    public function updateRecordsWithLanguageAll(): void
    {
        $tables = [
            Attribute::TABLE_NAME,
            Categoryscoring::TABLE_NAME,
            Download::TABLE_NAME,
            Fingerprint::TABLE_NAME,
            Ipinformation::TABLE_NAME,
            Linkclick::TABLE_NAME,
            Log::TABLE_NAME,
            Newsvisit::TABLE_NAME,
            Pagevisit::TABLE_NAME,
            Visitor::TABLE_NAME,
        ];
        foreach ($tables as $table) {
            $connection = DatabaseUtility::getConnectionForTable($table);
            $connection->executeQuery('update ' . $table . ' set sys_language_uid=-1');
        }
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws ExceptionDbal
     */
    public function removeVisitor(Visitor $visitor): void
    {
        $this->removeRelatedTableRowsByVisitor($visitor);

        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        $connection->executeQuery('delete from ' . Visitor::TABLE_NAME . ' where uid=' . (int)$visitor->getUid());
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws ExceptionDbal
     */
    public function removeRelatedTableRowsByVisitor(Visitor $visitor): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Fingerprint::TABLE_NAME);
        foreach ($visitor->getFingerprints() as $fingerprint) {
            $connection->executeQuery(
                'delete from ' . Fingerprint::TABLE_NAME . ' where uid=' . (int)$fingerprint->getUid()
            );
        }
        $tables = [
            Attribute::TABLE_NAME,
            Pagevisit::TABLE_NAME,
            Newsvisit::TABLE_NAME,
            Ipinformation::TABLE_NAME,
            Download::TABLE_NAME,
            Categoryscoring::TABLE_NAME,
            Log::TABLE_NAME,
            Linkclick::TABLE_NAME,
            Search::TABLE_NAME,
        ];
        foreach ($tables as $table) {
            $connection = DatabaseUtility::getConnectionForTable($table);
            $connection->executeQuery('delete from ' . $table . ' where visitor=' . (int)$visitor->getUid());
        }
    }

    public function truncateAll(): void
    {
        $tables = [
            Attribute::TABLE_NAME,
            Categoryscoring::TABLE_NAME,
            Company::TABLE_NAME,
            Download::TABLE_NAME,
            Fingerprint::TABLE_NAME,
            Ipinformation::TABLE_NAME,
            Log::TABLE_NAME,
            Linkclick::TABLE_NAME,
            Newsvisit::TABLE_NAME,
            Pagevisit::TABLE_NAME,
            Search::TABLE_NAME,
            Utm::TABLE_NAME,
            Visitor::TABLE_NAME,
            'tx_luxenterprise_domain_model_abpagevisit',
            'tx_luxenterprise_domain_model_shortenervisit',
        ];
        foreach ($tables as $table) {
            if (DatabaseUtility::isTableExisting($table)) {
                DatabaseUtility::getConnectionForTable($table)->truncate($table);
            }
        }
    }

    /**
     * @param FilterDto $filter
     * @param QueryInterface $query
     * @param array $logicalAnd
     * @return array
     * @throws InvalidQueryException
     * @throws Exception
     */
    protected function extendLogicalAndWithFilterConstraints(
        FilterDto $filter,
        QueryInterface $query,
        array $logicalAnd
    ): array {
        $logicalAnd[] = $query->logicalOr(
            // Also find leads without any pagevisits (e.g. with DNT header)
            $query->logicalAnd(
                $query->equals('pagevisits.uid', null),
                $query->greaterThan('crdate', $filter->getStartTimeForFilter()),
                $query->lessThan('crdate', $filter->getEndTimeForFilter())
            ),
            $query->logicalAnd(
                $query->greaterThan('pagevisits.crdate', $filter->getStartTimeForFilter()),
                $query->lessThan('pagevisits.crdate', $filter->getEndTimeForFilter()),
            )
        );

        if ($filter->isSearchtermSet()) {
            $logicalOr = [];
            foreach ($filter->getSearchterms() as $searchterm) {
                if (MathUtility::canBeInterpretedAsInteger($searchterm)) {
                    $logicalOr[] = $query->equals('uid', $searchterm);
                }
                $logicalOr[] = $query->like('email', '%' . $searchterm . '%');
                $logicalOr[] = $query->like('company', '%' . $searchterm . '%');
                $logicalOr[] = $query->like('ipAddress', '%' . $searchterm . '%');
                $logicalOr[] = $query->like('description', '%' . $searchterm . '%');
                $logicalOr[] = $query->like('attributes.value', '%' . $searchterm . '%');
            }
            $logicalAnd[] = $query->logicalOr(...$logicalOr);
        }
        if ($filter->isIdentifiedSet()) {
            $logicalAnd[] = $query->equals('identified', $filter->getIdentified() === FilterDto::IDENTIFIED_IDENTIFIED);
        }
        if ($filter->isPidSet()) {
            $logicalAnd[] = $query->equals('pagevisits.page.uid', (int)$filter->getPid());
        }
        if ($filter->isScoringSet()) {
            $logicalAnd[] = $query->greaterThan('scoring', $filter->getScoring());
        }
        if ($filter->isCategoryScoringSet()) {
            $logicalAnd[] = $query->equals('categoryscorings.category', $filter->getCategoryScoring());
            $logicalAnd[] = $query->greaterThan('categoryscorings.scoring', 0);
        }
        return $logicalAnd;
    }

    /**
     * @param FilterDto $filter
     * @return array
     */
    protected function getOrderingsArrayByFilterDto(FilterDto $filter): array
    {
        $orderings = ['identified' => QueryInterface::ORDER_DESCENDING];
        if ($filter->isCategoryScoringSet() === false) {
            $orderings['scoring'] = QueryInterface::ORDER_DESCENDING;
        } else {
            $orderings['categoryscorings.scoring'] = QueryInterface::ORDER_DESCENDING;
        }
        $orderings['tstamp'] = QueryInterface::ORDER_DESCENDING;
        return $orderings;
    }

    /**
     * @param FilterDto $filter
     * @param string $table
     * @param string $titleField
     * @param string $concatenation
     * @return string
     */
    protected function extendWhereClauseWithFilterSearchterms(
        FilterDto $filter,
        string $table = '',
        string $titleField = 'title',
        string $concatenation = 'and'
    ): string {
        $sql = '';
        if ($filter->isSearchtermSet()) {
            $tablePrefix = ($table !== '' ? $table . '.' : '');
            $or = [];
            foreach ($filter->getSearchterms() as $searchterm) {
                $searchterm = StringUtility::cleanString($searchterm);
                if ($sql === '') {
                    $sql .= ' ' . $concatenation . ' (';
                }

                if (MathUtility::canBeInterpretedAsInteger($searchterm)) {
                    $or[] = ' ' . $tablePrefix . 'uid = ' . (int)$searchterm;
                } else {
                    $or[] = ' ' . $tablePrefix . 'email like "%' . $searchterm . '%"';
                    $or[] = ' ' . $tablePrefix . 'company like "%' . $searchterm . '%"';
                    $or[] = ' ' . $tablePrefix . 'ip_address like "%' . $searchterm . '%"';
                    $or[] = ' ' . $tablePrefix . 'description like "%' . $searchterm . '%"';
                    $or[] = ' a.value like "%' . $searchterm . '%"';
                }
                $sql .= implode(' or ', $or);
            }

            $sql .= ')';
        }
        return $sql;
    }

    protected function extendWhereClauseWithFilterIdentified(FilterDto $filter): string
    {
        $sql = '';
        if ($filter->isIdentifiedSet()) {
            $sql .= ' and v.identified=' . (int)($filter->getIdentified() === FilterDto::IDENTIFIED_IDENTIFIED);
        }
        return $sql;
    }

    protected function extendWhereClauseWithFilterPid(FilterDto $filter): string
    {
        $sql = '';
        if ($filter->isPidSet()) {
            $sql .= ' and pv.page=' . (int)$filter->getPid();
        }
        return $sql;
    }

    /**
     * @param object $modifiedObject
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function update($modifiedObject)
    {
        if ($modifiedObject->getUid() > 0) {
            parent::update($modifiedObject);
        }
    }
}
