<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Ipinformation;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
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
     * @param string $idCookie
     * @return Visitor|null
     */
    public function findOneAndAlsoBlacklistedByIdCookie(string $idCookie)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true)->setEnableFieldsToBeIgnored(['blacklisted']);
        $query->matching($query->equals('idCookie', $idCookie));
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
     * @return string
     */
    public function findOneVisitorWithOutdatedCookieId(): string
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Visitor::TABLE_NAME);
        return (string)$queryBuilder
            ->select('uid')
            ->from(Visitor::TABLE_NAME)
            ->where('id_cookie != ""')
            ->setMaxResults(1)
            ->execute()
            ->fetchColumn(0);
    }

    /**
     * @return array
     */
    public function findVisitorsWithOutdatedCookieId(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Visitor::TABLE_NAME);
        return (array)$queryBuilder
            ->select('uid', 'id_cookie')
            ->from(Visitor::TABLE_NAME)
            ->where('id_cookie != ""')
            ->execute()
            ->fetchAll();
    }

    /**
     * @param FilterDto $filter
     * @return array
     * @throws InvalidQueryException
     */
    public function findAllWithKnownCompanies(FilterDto $filter): array
    {
        $visitors = $this->findAllWithIdentifiedFirst($filter);
        $visitorsWithCompanies = [];
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            if ($visitor->getCompany() !== '') {
                $visitorsWithCompanies[] = $visitor;
            }
        }
        return $visitorsWithCompanies;
    }

    /**
     * Find a small couple of hottest visitors
     *
     * @param FilterDto $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByHottestScorings(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = $this->extendLogicalAndWithFilterConstraints($filter, $query, []);
        $query->matching($query->logicalAnd($logicalAnd));
        $query->setLimit(7);
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
     * @param int $visitorUid
     * @return void
     * @throws DBALException
     */
    public function removeVisitorByVisitorUid(int $visitorUid)
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        $connection->query('delete from ' . Visitor::TABLE_NAME . ' where uid=' . (int)$visitorUid);
    }

    /**
     * @param int $visitorUid
     * @return void
     * @throws DBALException
     */
    public function removeRelatedTableRowsByVisitorUid(int $visitorUid)
    {
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
            $connection->query('delete from ' . $table . ' where visitor=' . (int)$visitorUid);
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
            Visitor::TABLE_NAME
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
     */
    protected function extendLogicalAndWithFilterConstraints(
        FilterDto $filter,
        QueryInterface $query,
        array $logicalAnd
    ): array {
        $logicalAnd[] = $query->greaterThan('pagevisits.crdate', $filter->getStartTimeForFilter());
        $logicalAnd[] = $query->lessThan('pagevisits.crdate', $filter->getEndTimeForFilter());
        if ($filter->getSearchterms() !== []) {
            $logicalOr = [];
            foreach ($filter->getSearchterms() as $searchterm) {
                $logicalOr[] = $query->like('email', '%' . $searchterm . '%');
                $logicalOr[] = $query->like('ipAddress', '%' . $searchterm . '%');
                $logicalOr[] = $query->like('idCookie', '%' . $searchterm . '%');
                $logicalOr[] = $query->like('referrer', '%' . $searchterm . '%');
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
}
