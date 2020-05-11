<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Ipinformation;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\DateUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class VisitorRepository
 */
class VisitorRepository extends AbstractRepository
{
    /**
     * Find a visitor by it's cookie and deliver also blacklisted visitors
     *
     * @param string $fingerprint
     * @param int $type
     * @return Visitor|null
     */
    public function findOneAndAlsoBlacklistedByFingerprint(
        string $fingerprint,
        int $type = Fingerprint::TYPE_FINGERPRINT
    ): ?Visitor {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true)->setEnableFieldsToBeIgnored(['blacklisted']);
        $and = [
            $query->equals('fingerprints.value', $fingerprint),
            $query->equals('fingerprints.type', $type)
        ];
        $query->matching($query->logicalAnd($and));
        $query->setOrderings(['crdate' => QueryInterface::ORDER_ASCENDING]);
        $query->setLimit(1);
        /** @var Visitor $visitor */
        $visitor = $query->execute()->getFirst();
        return $visitor;
    }

    /**
     * @param FilterDto $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findAllWithIdentifiedFirst(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = $this->extendLogicalAndWithFilterConstraints($filter, $query, []);
        $query->matching($query->logicalAnd($logicalAnd));
        $query->setOrderings($this->getOrderingsArrayByFilterDto($filter));
        return $query->execute();
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws InvalidQueryException
     * @throws Exception
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
     * @param string $propertyName
     * @param string $propertyValue
     * @param bool $exactMatch
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findAllByProperty(
        string $propertyName,
        string $propertyValue,
        bool $exactMatch
    ): QueryResultInterface {
        $query = $this->createQuery();
        $constraint = $query->equals($propertyName, $propertyValue);
        if ($exactMatch === false) {
            $constraint = $query->like($propertyName, '%' . $propertyValue . '%');
        }
        $query->matching($constraint);
        return $query->execute();
    }

    /**
     * Find a small couple of hottest visitors
     *
     * @param FilterDto $filter
     * @param int $limit
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByHottestScorings(FilterDto $filter, int $limit = 10): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = $this->extendLogicalAndWithFilterConstraints($filter, $query, []);
        $query->matching($query->logicalAnd($logicalAnd));
        $query->setLimit($limit);
        $query->setOrderings([
            'scoring' => QueryInterface::ORDER_DESCENDING,
            'tstamp' => QueryInterface::ORDER_DESCENDING
        ]);
        return $query->execute();
    }

    /**
     * @param string $email
     * @return QueryResultInterface
     */
    public function findDuplicatesByEmail(string $email): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('email', $email));
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
        $query = $this->createQuery();
        $query->matching($query->equals('pagevisits.page', $pageIdentifier));
        $query->setLimit(3);
        $query->setOrderings(['tstamp' => QueryInterface::ORDER_DESCENDING]);
        return $query->execute();
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
        $query->matching($query->logicalAnd($logicalAnd));
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
        $query->matching($query->logicalAnd($logicalAnd));
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
        $query->matching($query->logicalAnd($logicalAnd));
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
        $query->matching($query->logicalAnd($logicalAnd));
        return $query->execute();
    }

    /**
     * @param FilterDto $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findIdentifiedByMostVisits(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [$query->equals('identified', true)];
        $logicalAnd = $this->extendLogicalAndWithFilterConstraints($filter, $query, $logicalAnd);
        $query->matching($query->logicalAnd($logicalAnd));
        $query->setLimit(4);
        $query->setOrderings(['visits' => QueryInterface::ORDER_DESCENDING]);
        return $query->execute();
    }

    /**
     * Find visitors that are now on the website (5 Min last activity)
     *
     * @param int $limit
     * @return QueryResultInterface
     * @throws InvalidQueryException
     * @throws \Exception
     */
    public function findOnline(int $limit = 10): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->greaterThan('tstamp', DateUtility::getCurrentOnlineDateTime()->format('U')));
        $query->setLimit($limit);
        $query->setOrderings(['tstamp' => QueryInterface::ORDER_DESCENDING]);
        return $query->execute();
    }

    /**
     * Find visitors where tstamp is older then given timestamp
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
     * Find unknown visitors where tstamp is older then given timestamp
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
            $query->lessThan('tstamp', $timestamp)
        ];
        $query->matching($query->logicalAnd($logicalAnd));
        return $query->execute();
    }

    /**
     * @return int
     * @throws DBALException
     */
    public function findAllAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        return (int)$connection->executeQuery('select count(uid) from ' . Visitor::TABLE_NAME)->fetchColumn();
    }

    /**
     * @return int
     * @throws DBALException
     */
    public function findAllIdentifiedAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        return (int)$connection->executeQuery('select count(uid) from ' . Visitor::TABLE_NAME . ' where identified = 1')
            ->fetchColumn();
    }

    /**
     * @return int
     * @throws DBALException
     */
    public function findAllUnknownAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        return (int)$connection->executeQuery('select count(uid) from ' . Visitor::TABLE_NAME . ' where identified = 0')
            ->fetchColumn();
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws DBALException
     */
    public function removeVisitor(Visitor $visitor): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        $connection->query('delete from ' . Visitor::TABLE_NAME . ' where uid=' . (int)$visitor->getUid());
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws DBALException
     */
    public function removeRelatedTableRowsByVisitor(Visitor $visitor): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Fingerprint::TABLE_NAME);
        foreach ($visitor->getFingerprints() as $fingerprint) {
            $connection->query('delete from ' . Fingerprint::TABLE_NAME . ' where uid=' . (int)$fingerprint->getUid());
        }
        $tables = [
            Attribute::TABLE_NAME,
            Pagevisit::TABLE_NAME,
            Ipinformation::TABLE_NAME,
            Download::TABLE_NAME,
            Categoryscoring::TABLE_NAME,
            Log::TABLE_NAME
        ];
        foreach ($tables as $table) {
            $connection = DatabaseUtility::getConnectionForTable($table);
            $connection->query('delete from ' . $table . ' where visitor=' . (int)$visitor->getUid());
        }
    }

    /**
     * @return void
     */
    public function truncateAll()
    {
        $tables = [
            Attribute::TABLE_NAME,
            Pagevisit::TABLE_NAME,
            Ipinformation::TABLE_NAME,
            Download::TABLE_NAME,
            Categoryscoring::TABLE_NAME,
            Log::TABLE_NAME,
            Visitor::TABLE_NAME,
            Fingerprint::TABLE_NAME
        ];
        foreach ($tables as $table) {
            DatabaseUtility::getConnectionForTable($table)->truncate($table);
        }
    }

    /**
     * @param FilterDto $filter
     * @param QueryInterface $query
     * @param array $logicalAnd
     * @return array
     * @throws InvalidQueryException
     * @throws \Exception
     */
    protected function extendLogicalAndWithFilterConstraints(
        FilterDto $filter,
        QueryInterface $query,
        array $logicalAnd
    ): array {
        $logicalAnd[] = $query->greaterThan('tstamp', $filter->getStartTimeForFilter());
        $logicalAnd[] = $query->lessThan('tstamp', $filter->getEndTimeForFilter());
        if ($filter->getSearchterms() !== []) {
            $logicalOr = [];
            foreach ($filter->getSearchterms() as $searchterm) {
                if (MathUtility::canBeInterpretedAsInteger($searchterm)) {
                    $logicalOr[] = $query->equals('uid', $searchterm);
                }
                $logicalOr[] = $query->like('email', '%' . $searchterm . '%');
                $logicalOr[] = $query->like('ipAddress', '%' . $searchterm . '%');
                $logicalOr[] = $query->like('description', '%' . $searchterm . '%');
                $logicalOr[] = $query->like('attributes.value', '%' . $searchterm . '%');
            }
            $logicalAnd[] = $query->logicalOr($logicalOr);
        }
        if ($filter->getIdentified() > FilterDto::IDENTIFIED_ALL) {
            $query->equals('identified', $filter->getIdentified() === FilterDto::IDENTIFIED_IDENTIFIED);
        }
        if ($filter->getPid() !== '') {
            $logicalAnd[] = $query->equals('pagevisits.page.uid', (int)$filter->getPid());
        }
        if ($filter->getScoring() > 0) {
            $logicalAnd[] = $query->greaterThan('scoring', $filter->getScoring());
        }
        if ($filter->getCategoryScoring() !== null) {
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
        if ($filter->getCategoryScoring() === null) {
            $orderings['scoring'] = QueryInterface::ORDER_DESCENDING;
        } else {
            $orderings['categoryscorings.scoring'] = QueryInterface::ORDER_DESCENDING;
        }
        $orderings['tstamp'] = QueryInterface::ORDER_DESCENDING;
        return $orderings;
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
