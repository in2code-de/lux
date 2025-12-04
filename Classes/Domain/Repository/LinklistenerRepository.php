<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class LinklistenerRepository extends AbstractRepository
{
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * @param FilterDto $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByFilter(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForCrdate($filter, $query, []);
        $logicalAnd = $this->extendWithExtendedFilterQuery($filter, $query, $logicalAnd);
        $query->matching($query->logicalAnd(...$logicalAnd));
        return $query->execute();
    }

    /**
     * @param FilterDto $filter
     * @param QueryInterface $query
     * @param array $logicalAnd
     * @return array
     * @throws InvalidQueryException
     */
    protected function extendLogicalAndWithFilterConstraintsForCrdate(
        FilterDto $filter,
        QueryInterface $query,
        array $logicalAnd
    ): array {
        $or = [
            $query->logicalAnd(
                $query->greaterThan('linkclicks.crdate', $filter->getStartTimeForFilter()),
                $query->lessThan('linkclicks.crdate', $filter->getEndTimeForFilter()),
            ),
        ];
        if ($filter->isTimeFromOrTimeToSet() === false) { // add unused linklisteners (without clicks) per default
            $or[] = $query->logicalAnd(
                $query->equals('linkclicks.uid', null),
                $query->greaterThan('crdate', $filter->getStartTimeForFilter()),
                $query->lessThan('crdate', $filter->getEndTimeForFilter())
            );
        }
        $logicalAnd[] = $query->logicalOr(...$or);
        return $logicalAnd;
    }

    /**
     * @param FilterDto $filter
     * @param QueryInterface $query
     * @param array $logicalAnd
     * @return array
     * @throws InvalidQueryException
     */
    protected function extendWithExtendedFilterQuery(
        FilterDto $filter,
        QueryInterface $query,
        array $logicalAnd
    ): array {
        if ($filter->isSearchtermSet()) {
            $logicalOr = [];
            foreach ($filter->getSearchterms() as $searchterm) {
                if (MathUtility::canBeInterpretedAsInteger($searchterm)) {
                    $logicalOr[] = $query->equals('uid', $searchterm);
                } else {
                    $logicalOr[] = $query->like('title', '%' . $searchterm . '%');
                    $logicalOr[] = $query->like('description', '%' . $searchterm . '%');
                    $logicalOr[] = $query->like('category.title', '%' . $searchterm . '%');
                }
            }
            $logicalAnd[] = $query->logicalOr(...$logicalOr);
        }
        if ($filter->isCategoryScoringSet()) {
            $logicalAnd[] = $query->equals('category', $filter->getCategoryScoring());
        }

        $logicalAnd[] = $query->logicalOr(
            $query->equals('linkclicks', 0),
            $query->in('linkclicks.site', $filter->getSitesForFilter())
        );
        return $logicalAnd;
    }
}
