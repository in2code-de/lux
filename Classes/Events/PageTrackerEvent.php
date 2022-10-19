<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;

final class PageTrackerEvent
{
    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * @var Pagevisit
     */
    protected $pagevisit;

    /**
     * @param Visitor $visitor
     * @param Pagevisit $pagevisit
     */
    public function __construct(Visitor $visitor, Pagevisit $pagevisit)
    {
        $this->visitor = $visitor;
        $this->pagevisit = $pagevisit;
    }

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * @return Pagevisit
     */
    public function getPagevisit(): Pagevisit
    {
        return $this->pagevisit;
    }
}
