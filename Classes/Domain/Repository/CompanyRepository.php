<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use DateTime;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Company;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\DatabaseUtility;

class CompanyRepository extends AbstractRepository
{
    /**
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function findByFilter(FilterDto $filter): array
    {
        $sql = 'select c.uid,sum(v.scoring) companyscoring'
            . ' from tx_lux_domain_model_company c'
            . ' left join tx_lux_domain_model_visitor v on v.companyrecord = c.uid'
            . ' left join tx_lux_domain_model_pagevisit pv on pv.visitor = v.uid'
            . ' where c.deleted=0 and v.deleted=0 and v.blacklisted=0';
        $sql .= $this->extendWhereClauseWithFilterSearchterms($filter, 'c');
        $sql .= $this->extendWhereClauseWithFilterBranchCode($filter);
        $sql .= $this->extendWhereClauseWithFilterCompanyTime($filter, true, 'pv');
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

    /**
     * @param Company $company
     * @return void
     * @throws ExceptionDbal
     */
    public function findLatestPageVisitToCompany(Company $company): ?DateTime
    {
        $sql = 'select pv.crdate'
            . ' from tx_lux_domain_model_company c'
            . ' left join tx_lux_domain_model_visitor v on v.companyrecord = c.uid'
            . ' left join tx_lux_domain_model_pagevisit pv on pv.visitor = v.uid'
            . ' where c.uid=' . $company->getUid() . ' and c.deleted=0 and v.deleted=0'
            . ' and v.blacklisted=0 and pv.deleted=0'
            . ' order by pv.crdate desc'
            . ' limit 1';
        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        $timestamp = $connection->executeQuery($sql)->fetchOne();
        if ($timestamp !== false) {
            return DateTime::createFromFormat('U', (string)$timestamp);
        }
        return null;
    }

    public function findNumberOfPagevisitsByCompany(Company $company): int
    {
        $sql = 'select count(pv.uid)'
            . ' from tx_lux_domain_model_company c'
            . ' left join tx_lux_domain_model_visitor v on v.companyrecord = c.uid'
            . ' left join tx_lux_domain_model_pagevisit pv on pv.visitor = v.uid'
            . ' where c.uid=' . $company->getUid() . ' and c.deleted=0 and v.deleted=0'
            . ' and v.blacklisted=0 and pv.deleted=0'
            . ' limit 1';
        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    public function findNumberOfVisitorsByCompany(Company $company): int
    {
        $sql = 'select count(c.uid)'
            . ' from tx_lux_domain_model_company c'
            . ' left join tx_lux_domain_model_visitor v on v.companyrecord = c.uid'
            . ' where c.uid=' . $company->getUid() . ' and c.deleted=0 and v.deleted=0 and v.blacklisted=0'
            . ' limit 1';
        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    /**
     * Example result:
     *  [
     *      8421 => 'Auswärtige Angelegenheiten',
     *      72 => 'Forschung und Entwicklung',
     *  ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function findAllBranches(FilterDto $filter): array
    {
        $sql = 'select c.branch_code, c.branch'
            . ' from tx_lux_domain_model_company c'
            . ' where c.deleted=0 and c.branch != \'\' and c.branch_code != 0'
            . ' group by c.branch_code,c.branch'
            . ' order by c.branch asc';
        $connection = DatabaseUtility::getConnectionForTable(Company::TABLE_NAME);
        return $connection->executeQuery($sql)->fetchAllKeyValue();
    }

    protected function extendWhereClauseWithFilterBranchCode(FilterDto $filter): string
    {
        $sql = '';
        if ($filter->getBranchCode() > 0) {
            $sql .= ' and branch_code = ' . $filter->getBranchCode();
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
