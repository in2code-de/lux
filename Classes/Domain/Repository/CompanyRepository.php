<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\Exception as ExceptionDbal;
use Exception;
use In2code\Lux\Domain\Model\Company;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\BranchService;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\DateUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class CompanyRepository extends AbstractRepository
{
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function findByFilter(FilterDto $filter): array
    {
        $sql = 'select c.uid,sum(v.scoring) companyscoring'
            . ' from ' . Company::TABLE_NAME . ' c'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.companyrecord = c.uid'
            . $this->extendJoinToPagevisitsForPagevisitTimeFilter($filter)
            . ' where c.deleted=0 and v.deleted=0 and v.blacklisted=0';
        $sql .= $this->extendWhereClauseWithFilterSearchterms($filter, 'c');
        $sql .= $this->extendWhereClauseWithFilterBranchCode($filter);
        $sql .= $this->extendWhereClauseWithFilterCategory($filter, 'c');
        $sql .= ' group by c.uid';
        $sql .= $this->extendWhereClauseWithFilterCompanyscoring($filter);
        $sql .= ' order by companyscoring desc';
        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        $results = $connection->executeQuery($sql)->fetchAllKeyValue();

        $companies = [];
        foreach ($results as $identifier => $scoring) {
            /** @var Company $company */
            $company = $this->findByUid($identifier);
            if ($company !== null) {
                $company->setScoring((int)$scoring);
                $companies[] = $company;
            }
        }
        return $companies;
    }

    public function findByTitleAndDomain(string $title, string $domain): ?Company
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('title', $title),
                $query->equals('domain', $domain)
            )
        );
        $query->setLimit(1);
        return $query->execute()->getFirst();
    }

    public function findNumberOfPagevisitsByCompany(Company $company): int
    {
        $sql = 'select count(pv.uid)'
            . ' from ' . Company::TABLE_NAME . ' c'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.companyrecord = c.uid'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on pv.visitor = v.uid'
            . ' where c.uid=' . $company->getUid() . ' and c.deleted=0 and v.deleted=0'
            . ' and v.blacklisted=0 and pv.deleted=0'
            . ' limit 1';
        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    public function findNumberOfVisitorsByCompany(Company $company): int
    {
        $sql = 'select count(c.uid)'
            . ' from ' . Company::TABLE_NAME . ' c'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.companyrecord = c.uid'
            . ' where c.uid=' . $company->getUid() . ' and c.deleted=0 and v.deleted=0 and v.blacklisted=0'
            . ' limit 1';
        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    /**
     * Example result:
     *  [
     *      84 => 'Auswärtige Angelegenheiten',
     *      72 => 'Forschung und Entwicklung',
     *  ]
     *
     * @return array
     * @throws ExceptionDbal
     */
    public function findAllBranches(): array
    {
        $branches = [];
        $sql = 'select c.branch_code'
            . ' from ' . Company::TABLE_NAME . ' c'
            . ' where c.deleted=0 and c.branch_code != "00" and c.branch_code != \'\''
            . ' group by c.branch_code';
        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        $result = $connection->executeQuery($sql)->fetchAllAssociative();

        $branchService = GeneralUtility::makeInstance(BranchService::class);
        foreach ($result as $row) {
            $branches[$row['branch_code']] = $branchService->getBranchNameByCode($row['branch_code']);
        }
        asort($branches);
        return $branches;
    }

    /**
     *  [
     *      '1' => 234,
     *      '2' => 123,
     *      '3' => 5,
     *      '4' => 33,
     *      '5' => 45,
     *      '6' => 876,
     *  ]
     *
     * @param FilterDto $filter
     * @param int $limit
     * @return array
     * @throws ExceptionDbal
     */
    public function findRevenueClasses(FilterDto $filter, int $limit = 6): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        $sql = 'select c.revenue_class, count(c.revenue_class) count'
            . ' from ' . Company::TABLE_NAME . ' c'
            . ' left join ' . Visitor::TABLE_NAME . ' v on v.companyrecord = c.uid'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on pv.visitor = v.uid'
            . ' where c.revenue_class != \'\''
            . $this->extendWhereClauseWithFilterCompanyTime($filter)
            . ' group by revenue_class having (count > 1) order by count desc limit 100';
        $records = $connection->executeQuery($sql)->fetchAllKeyValue();
        return array_slice($records, 0, $limit);
    }

    /**
     *  [
     *      9 => 97, // September
     *      10 => 113, // October
     *      11 => 123, // November
     *  ]
     *
     * @return array
     * @throws Exception
     */
    public function findCompanyAmountOfLastSixMonths(): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        $months = DateUtility::getLatestMonthDatesMultiple(6);
        $amounts = [];
        foreach ($months as $month) {
            $sql = 'select count(distinct c.uid)'
                . ' from ' . Company::TABLE_NAME . ' c'
                . ' left join ' . Visitor::TABLE_NAME . ' v on v.companyrecord = c.uid'
                . ' left join ' . Pagevisit::TABLE_NAME . ' pv on pv.visitor = v.uid'
                . ' where pv.crdate <= ' . $month[1]->format('U')
                . ' and c.deleted=0 and v.deleted=0 and v.blacklisted=0 and pv.deleted=0';
            $amounts[$month[0]->format('n')] = $connection->executeQuery($sql)->fetchOne();
        }
        $amounts = array_reverse($amounts, true);
        return $amounts;
    }

    public function getAllAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        return (int)$connection->executeQuery(
            'select count(uid) from ' . Company::TABLE_NAME . ' where hidden=0 and deleted=0;'
        )->fetchOne();
    }

    /**
     * @param Company $company
     * @param bool $removeVisitors
     * @return void
     * @throws ExceptionDbal
     */
    public function removeCompany(Company $company, bool $removeVisitors): void
    {
        if ($removeVisitors) {
            $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
            foreach ($company->getVisitors() as $visitor) {
                $visitorRepository->removeVisitor($visitor);
            }
        }

        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        $connection->executeQuery('delete from ' . Company::TABLE_NAME . ' where uid=' . (int)$company->getUid());
    }

    protected function extendWhereClauseWithFilterBranchCode(FilterDto $filter): string
    {
        $sql = '';
        if ($filter->getBranchCode() > 0) {
            $sql .= ' and branch_code = ' . $filter->getBranchCode();
        }
        return $sql;
    }

    protected function extendWhereClauseWithFilterCategory(FilterDto $filter, string $table = ''): string
    {
        $sql = '';
        if ($filter->getCategory() !== null) {
            if ($table !== '') {
                $table .= '.';
            }
            $sql .= ' and ' . $table . 'category = ' . $filter->getCategory()->getUid();
        }
        return $sql;
    }

    /**
     * Building subquery for filter by time to keep scoring as it is
     *
     * @param FilterDto $filter
     * @return string
     */
    protected function extendJoinToPagevisitsForPagevisitTimeFilter(FilterDto $filter): string
    {
        $sql = '';
        if ($filter->isTimeFromOrTimeToSet()) {
            $sql = ' left join (
                select distinct pv.visitor
                from ' . Pagevisit::TABLE_NAME . ' pv
                where 1' . $this->extendWhereClauseWithFilterCompanyTime($filter) . '
            ) subquery ON subquery.visitor = v.uid';
        }
        return $sql;
    }

    protected function extendWhereClauseWithFilterCompanyTime(FilterDto $filter): string
    {
        $sql = '';
        if ($filter->isTimeFromOrTimeToSet()) {
            if ($filter->getTimeFrom() !== '') {
                $sql .= ' and pv.crdate >= ' . $filter->getTimeFromDateTime()->format('U');
            }
            if ($filter->getTimeTo() !== '') {
                $sql .= ' and pv.crdate <= ' . $filter->getTimeToDateTime()->format('U');
            }
        }
        return $sql;
    }

    protected function extendWhereClauseWithFilterCompanyscoring(FilterDto $filter): string
    {
        $sql = '';
        if ($filter->getScoring() > 0) {
            $sql .= ' having companyscoring >= ' . $filter->getScoring();
        }
        return $sql;
    }
}
