<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Exception;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

abstract class AbstractRepository extends Repository
{
    protected $defaultOrderings = [
        'crdate' => QueryInterface::ORDER_DESCENDING,
    ];

    public function initializeObject(): void
    {
        /** @var Typo3QuerySettings $defaultQuerySettings */
        $defaultQuerySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $defaultQuerySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($defaultQuerySettings);
    }

    public function persistAll(): void
    {
        $persistanceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $persistanceManager->persistAll();
    }

    /**
     * @param array $identifiers
     * @param string $tableName
     * @return array must be an array - otherwise pagebrowser seems to be broken (for whatever reason)
     */
    protected function convertIdentifiersToObjects(array $identifiers, string $tableName): array
    {
        $identifierList = implode(',', $identifiers);
        $sql = 'select * from ' . $tableName . ' where uid in (' . $identifierList . ')'
            . 'ORDER BY FIELD(uid, ' . $identifierList . ')';
        $query = $this->createQuery();
        $query = $query->statement($sql);
        return $query->execute()->toArray();
    }

    /**
     * @param FilterDto $filter
     * @param QueryInterface $query
     * @param array $logicalAnd
     * @return array
     * @throws InvalidQueryException
     * @throws Exception
     */
    protected function extendLogicalAndWithFilterConstraintsForCrdate(
        FilterDto $filter,
        QueryInterface $query,
        array $logicalAnd
    ): array {
        $logicalAnd[] = $query->greaterThan('crdate', $filter->getStartTimeForFilter());
        $logicalAnd[] = $query->lessThan('crdate', $filter->getEndTimeForFilter());
        return $logicalAnd;
    }

    /**
     * @param FilterDto $filter
     * @param QueryInterface $query
     * @param array $logicalAnd
     * @param string $tablePrefix
     * @return array
     * @throws InvalidQueryException
     */
    protected function extendLogicalAndWithFilterConstraintsForSite(
        FilterDto $filter,
        QueryInterface $query,
        array $logicalAnd,
        string $tablePrefix = ''
    ): array {
        $field = ($tablePrefix ? $tablePrefix . '.' : '') . 'site';
        $logicalAnd[] = $query->in($field, $filter->getSitesForFilter());
        return $logicalAnd;
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
            foreach ($filter->getSearchterms() as $searchterm) {
                if ($sql === '') {
                    $sql .= ' ' . $concatenation . ' (';
                } else {
                    $sql .= ' or ';
                }

                if (MathUtility::canBeInterpretedAsInteger($searchterm)) {
                    $sql .= ($table !== '' ? $table . '.' : '') . 'uid = ' . (int)$searchterm;
                } else {
                    $sql .= ($table !== '' ? $table . '.' : '') . $titleField . ' like "%'
                        . StringUtility::cleanString($searchterm) . '%"';
                }
            }
            $sql .= ')';
        }
        return $sql;
    }

    /**
     * Returns part of a where clause like
     *      " and crdate > 1223455 and crdate < 987876"
     *
     * @param FilterDto $filter
     * @param bool $andPrefix
     * @param string $table table with crdate (normally the main table)
     * @return string
     * @throws Exception
     */
    protected function extendWhereClauseWithFilterTime(
        FilterDto $filter,
        bool $andPrefix = true,
        string $table = ''
    ): string {
        $field = 'crdate';
        if ($table !== '') {
            $field = $table . '.' . $field;
        }
        $string = '';
        if ($andPrefix === true) {
            $string .= ' and ';
        }
        $string .= $field . '>' . $filter->getStartTimeForFilter()->format('U')
            . ' and ' . $field . '<' . $filter->getEndTimeForFilter()->format('U');
        return $string;
    }

    /**
     * Returns part of a where clause like
     *      ' and domain="in2code.de"'
     *
     * @param FilterDto $filter
     * @param string $table table with crdate (normally the main table)
     * @return string
     * @throws Exception
     */
    protected function extendWhereClauseWithFilterDomain(FilterDto $filter, string $table = ''): string
    {
        $sql = '';
        if ($filter->isDomainSet()) {
            $field = 'domain';
            if ($table !== '') {
                $field = $table . '.' . $field;
            }
            $sql .= ' and ' . $field . '="' . $filter->getDomain() . '"';
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
        $field = 'site';
        if ($table !== '') {
            $field = $table . '.' . $field;
        }
        return ' and ' . $field . ' in ("' . implode('","', $filter->getSitesForFilter()) . '")';
    }

    /**
     * Returns part of a where clause like
     *      " and v.scoring >= 90"
     *
     * @param FilterDto $filter
     * @param string $table table name of the visitor table
     * @return string
     */
    protected function extendWhereClauseWithFilterScoring(FilterDto $filter, string $table = ''): string
    {
        $sql = '';
        if ($filter->isScoringSet()) {
            $field = 'scoring';
            if ($table !== '') {
                $field = $table . '.' . $field;
            }
            $sql .= ' and ' . $field . ' >= ' . $filter->getScoring();
        }
        return $sql;
    }

    /**
     * Returns part of a where clause like
     *      " and v.uid = 123"
     *
     * @param FilterDto $filter
     * @param string $table table name of the visitor table
     * @param string $field
     * @return string
     */
    protected function extendWhereClauseWithFilterVisitor(
        FilterDto $filter,
        string $table = '',
        string $field = 'uid'
    ): string {
        $sql = '';
        if ($table !== '') {
            $field = $table . '.' . $field;
        }
        if ($filter->isVisitorSet()) {
            $sql = ' and ' . $field . ' = ' . $filter->getVisitor()->getUid();
        }
        if ($filter->isCompanySet()) {
            $sql = '';
            foreach ($filter->getCompany()->getVisitors() as $visitor) {
                if ($visitor !== null) {
                    if ($sql === '') {
                        $sql = ' and (';
                    } else {
                        $sql .= ' or ';
                    }
                    $sql .= $field . ' = ' . $visitor->getUid();
                }
            }
            $sql .= ')';
        }
        return $sql;
    }

    /**
     * Returns part of a where clause like
     *      " and cs.category = 4"
     *
     * @param FilterDto $filter
     * @param string $table table name of the categoryscoring table
     * @return string
     */
    protected function extendWhereClauseWithFilterCategoryScoring(FilterDto $filter, string $table = ''): string
    {
        $sql = '';
        if ($filter->isCategoryScoringSet()) {
            $field = 'category';
            if ($table !== '') {
                $field = $table . '.' . $field;
            }
            $sql .= ' and ' . $field . ' = ' . $filter->getCategoryScoring()->getUid();
        }
        return $sql;
    }

    /**
     * This function allows us to build only a join if this is needed by filter settings (to increase performance)
     *
     * @param FilterDto $filter
     * @param array $tables Define which joins should be build in general
     * @return string
     */
    protected function extendFromClauseWithJoinByFilter(
        FilterDto $filter,
        array $tables = ['pv', 'p', 'cs', 'v']
    ): string {
        $sql = '';
        if (in_array('v', $tables)) {
            $sql .= ' left join ' . Visitor::TABLE_NAME . ' v on v.uid = pv.visitor';
        }
        if ($filter->isSearchtermSet() || $filter->isDomainSet()) {
            if (in_array('pv', $tables)) {
                $sql .= ' left join ' . Pagevisit::TABLE_NAME . ' pv on v.uid = pv.visitor';
            }
            if (in_array('p', $tables)) {
                $sql .= ' left join ' . Page::TABLE_NAME . ' p on p.uid = pv.page';
            }
        }
        if ($filter->isCategoryScoringSet()) {
            if (in_array('cs', $tables)) {
                $sql .= ' left join ' . Categoryscoring::TABLE_NAME . ' cs on v.uid = cs.visitor';
            }
        }
        return $sql;
    }
}
