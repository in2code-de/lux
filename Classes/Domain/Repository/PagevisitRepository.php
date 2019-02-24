<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class PagevisitRepository
 */
class PagevisitRepository extends AbstractRepository
{

    /**
     * Find by single page entries with all pagevisits ordered by number of pagevisits with a limit of 100
     *
     * @param FilterDto $filter
     * @return array
     * @throws InvalidQueryException
     */
    public function findCombinedByPageIdentifier(FilterDto $filter): array
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd([
                $query->greaterThan('crdate', $filter->getStartTimeForFilter()),
                $query->lessThan('crdate', $filter->getEndTimeForFilter()),
                $query->greaterThan('page.uid', 0),
            ])
        );
        $pages = $query->execute(true);
        $result = $this->combineAndCutPages($pages);
        return $result;
    }

    /**
     * @param FilterDto $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findLatestPagevisits(FilterDto $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd([
                $query->greaterThan('crdate', $filter->getStartTimeForFilter()),
                $query->lessThan('crdate', $filter->getEndTimeForFilter()),
                $query->greaterThan('page.uid', 0)
            ])
        );
        $query->setLimit(5);
        return $query->execute();
    }

    /**
     * Get the number of visitors of the last 8 days
     *      Example return
     *          [10,52,8,54,536,15,55,44] or
     *          [numberVisitorsToday,numberVisitorsYesterday,...]
     *
     * @return array
     * @throws InvalidQueryException
     */
    public function getNumberOfVisitorsByDay(): array
    {
        $frames = [
            [
                new \DateTime('today midnight'),
                new \DateTime()
            ],
            [
                new \DateTime('yesterday midnight'),
                new \DateTime('today midnight')
            ],
            [
                new \DateTime('2 days ago midnight'),
                new \DateTime('yesterday midnight')
            ],
            [
                new \DateTime('3 days ago midnight'),
                new \DateTime('2 days ago midnight')
            ],
            [
                new \DateTime('4 days ago midnight'),
                new \DateTime('3 days ago midnight')
            ],
            [
                new \DateTime('5 days ago midnight'),
                new \DateTime('4 days ago midnight')
            ],
            [
                new \DateTime('6 days ago midnight'),
                new \DateTime('5 days ago midnight')
            ],
            [
                new \DateTime('7 days ago midnight'),
                new \DateTime('6 days ago midnight')
            ]
        ];
        $frames = array_reverse($frames);
        $visits = [];
        foreach ($frames as $frame) {
            $query = $this->createQuery();
            $query->matching(
                $query->logicalAnd([
                    $query->greaterThan('crdate', $frame[0]),
                    $query->lessThan('crdate', $frame[1])
                ])
            );
            $visits[] = $query->execute()->count();
        }
        return $visits;
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
     * @param Page $page
     * @return QueryResultInterface
     */
    public function findByPage(Page $page): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('page', $page));
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        $query->setLimit(100);
        return $query->execute();
    }

    /**
     * @param $pages
     * @return array
     */
    protected function combineAndCutPages($pages): array
    {
        $result = [];
        foreach ($pages as $pageProperties) {
            $result[$pageProperties['page']][] = $pageProperties;
        }
        array_multisort(array_map('count', $result), SORT_DESC, $result);
        $result = array_slice($result, 0, 100);
        return $result;
    }
}
