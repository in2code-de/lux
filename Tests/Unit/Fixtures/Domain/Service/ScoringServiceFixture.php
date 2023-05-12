<?php

namespace In2code\Lux\Tests\Unit\Fixtures\Domain\Service;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\ScoringService;

class ScoringServiceFixture extends ScoringService
{
    public int $numberOfSiteVisits = 0;
    public int $numberOfVisits = 0;
    public int $numberOfDaysSinceLastVisit = 0;
    public int $numberOfDownloads = 0;

    protected function getNumberOfSiteVisits(Visitor $visitor): int
    {
        return $this->numberOfSiteVisits;
    }

    protected function getNumberOfVisits(Visitor $visitor): int
    {
        return $this->numberOfVisits;
    }

    protected function getNumberOfDaysSinceLastVisit(Visitor $visitor): int
    {
        return $this->numberOfDaysSinceLastVisit;
    }

    protected function getNumberOfDownloads(Visitor $visitor): int
    {
        return $this->numberOfDownloads;
    }

    public function setCalculation(): void
    {
        $this->calculation =
            '(10 * numberOfSiteVisits) + (1 * numberOfPageVisits) + (20 * downloads) - (1 * lastVisitDaysAgo)';
    }
}
