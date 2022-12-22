<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;

final class PageTrackerEvent
{
    protected Visitor $visitor;
    protected Pagevisit $pagevisit;

    public function __construct(Visitor $visitor, Pagevisit $pagevisit)
    {
        $this->visitor = $visitor;
        $this->pagevisit = $pagevisit;
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getPagevisit(): Pagevisit
    {
        return $this->pagevisit;
    }
}
