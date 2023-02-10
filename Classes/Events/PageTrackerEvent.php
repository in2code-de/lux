<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;

final class PageTrackerEvent
{
    protected Visitor $visitor;
    protected Pagevisit $pagevisit;

    protected array $arguments;

    public function __construct(Visitor $visitor, Pagevisit $pagevisit, array $arguments)
    {
        $this->visitor = $visitor;
        $this->pagevisit = $pagevisit;
        $this->arguments = $arguments;
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getPagevisit(): Pagevisit
    {
        return $this->pagevisit;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
