<?php

namespace In2code\Lux\Tests\Unit\Fixtures\Domain\Service;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\ScoringService;

/**
 * Class BackendUtilityFixture
 */
class ScoringServiceFixture extends ScoringService
{
    /**
     * @var int
     */
    public $numberOfSiteVisits = 0;

    /**
     * @var int
     */
    public $numberOfVisits = 0;

    /**
     * @var int
     */
    public $numberOfDaysSinceLastVisit = 0;

    /**
     * @var int
     */
    public $numberOfDownloads = 0;

    /**
     * @param Visitor $visitor
     * @return int
     */
    protected function getNumberOfSiteVisits(Visitor $visitor): int
    {
        return $this->numberOfSiteVisits;
    }

    /**
     * @param Visitor $visitor
     * @return int
     */
    protected function getNumberOfVisits(Visitor $visitor): int
    {
        return $this->numberOfVisits;
    }

    /**
     * @param Visitor $visitor
     * @return int
     */
    protected function getNumberOfDaysSinceLastVisit(Visitor $visitor): int
    {
        return $this->numberOfDaysSinceLastVisit;
    }

    /**
     * @param Visitor $visitor
     * @return int
     */
    protected function getNumberOfDownloads(Visitor $visitor): int
    {
        return $this->numberOfDownloads;
    }

    /**
     * @return void
     */
    public function setCalculation()
    {
        $this->calculation = '(10 * numberOfSiteVisits) + (1 * numberOfPageVisits) + (20 * downloads) - (1 * lastVisitDaysAgo)';
    }
}
