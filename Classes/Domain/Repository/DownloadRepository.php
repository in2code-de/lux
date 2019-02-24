<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class DownloadRepository
 */
class DownloadRepository extends AbstractRepository
{

    /**
     * Find all combined by href ordered by number of downloads with a limit of 100
     *
     * @param FilterDto $filter
     * @return array
     * @throws InvalidQueryException
     */
    public function findCombinedByHref(FilterDto $filter): array
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd([
                $query->greaterThan('crdate', $filter->getStartTimeForFilter()),
                $query->lessThan('crdate', $filter->getEndTimeForFilter())
            ])
        );
        $assets = $query->execute(true);
        $result = [];
        /** @var Download $asset */
        foreach ($assets as $asset) {
            $result[$asset['href']][] = $asset;
        }
        array_multisort(array_map('count', $result), SORT_DESC, $result);
        $result = array_slice($result, 0, 100);
        return $result;
    }

    /**
     * Find all downloads of a visitor but with a given time. If a visitor would download an asset every single day
     * since a week ago (so also today) and the given time is yesterday, we want to get all downloads but not from
     * today.
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
     * Get the number of downloads of the last 8 days
     *      Example return
     *          [10,52,8,54,536,15,55,44] or
     *          [numberOfDownloadsToday,numberOfDownloadsYesterday,...]
     *
     * @return array
     * @throws InvalidQueryException
     */
    public function getNumberOfDownloadsByDay(): array
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
        $downloads = [];
        foreach ($frames as $frame) {
            $query = $this->createQuery();
            $query->matching(
                $query->logicalAnd([
                    $query->greaterThan('crdate', $frame[0]),
                    $query->lessThan('crdate', $frame[1])
                ])
            );
            $downloads[] = $query->execute()->count();
        }
        return $downloads;
    }
}
