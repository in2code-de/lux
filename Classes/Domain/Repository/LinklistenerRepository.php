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
        $logicalAnd = [$query->greaterThan('uid', 0)];
        if ($filter->isSet()) {
            $logicalAnd[] = $query->greaterThan('linkclicks.crdate', $filter->getStartTimeForFilter());
            $logicalAnd[] = $query->lessThan('linkclicks.crdate', $filter->getEndTimeForFilter());
        }
        $logicalAnd = $this->extendWithExtendedFilterQuery($query, $logicalAnd, $filter);
        $query->matching($query->logicalAnd(...$logicalAnd));
        return $query->execute();
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
                        $logicalOr[] = $query->equals('uid', $searchterm);
                    } else {
                        $logicalOr[] = $query->like('title', '%' . $searchterm . '%');
                        $logicalOr[] = $query->like('description', '%' . $searchterm . '%');
                        $logicalOr[] = $query->like('category.title', '%' . $searchterm . '%');
                    }
                }
                $logicalAnd[] = $query->logicalOr(...$logicalOr);
            }
            if ($filter->getCategoryScoring() !== null) {
                $logicalAnd[] = $query->equals('category', $filter->getCategoryScoring());
            }
        }
        return $logicalAnd;
    }
}
