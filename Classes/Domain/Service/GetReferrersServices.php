<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Service;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Domain\Service\Referrer\SourceHelper;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class GetReferrersServices
{
    public function __construct(
        protected readonly PageVisitRepository $pageVisitRepository,
        protected readonly SourceHelper $sourceHelper
    ) {
    }

    /**
     *  [
     *      [
     *          'referrer_domain' => 'x.com',
     *          'count' => 123,
     *          'identified_count' => 34,
     *      ],
     *      [
     *          'referrer_domain' => 'openai.com',
     *          'count' => 25,
     *          'identified_count' => 12,
     *      ],
     *  ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function getReferrers(FilterDto $filter): array
    {
        $referrers = $this->pageVisitRepository->getAmountOfReferrerDomains($filter);
        $grouped = $this->groupReferrers($referrers);
        return $this->sortReferrers($grouped);
    }

    protected function groupReferrers(array $referrers): array
    {
        $grouped = [];
        foreach ($referrers as $row) {
            $key = $this->sourceHelper->getKeyFromHost($row['referrer_domain']) ?: 'other';
            $readableReferrer = $this->sourceHelper->getReadableReferrer($row['referrer_domain']);

            if (isset($grouped[$key][$readableReferrer]) === false) {
                $grouped[$key][$readableReferrer] = $row;
                continue;
            }

            // merge referrers
            $grouped[$key][$readableReferrer]['count'] += $row['count'];
            $grouped[$key][$readableReferrer]['identified_count'] += $row['identified_count'];
        }
        return $grouped;
    }

    protected function sortReferrers(array $grouped): array
    {
        // Ensure "other" key is always the last key
        if (isset($grouped['other'])) {
            $otherValue = $grouped['other'];
            unset($grouped['other']);
            $grouped['other'] = $otherValue;
        }

        return $grouped;
    }
}
