<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Visitor;

final class NewsTrackerEvent
{
    protected Visitor $visitor;
    protected Newsvisit $newsvisit;

    protected array $arguments;

    public function __construct(Visitor $visitor, Newsvisit $newsvisit, array $arguments)
    {
        $this->visitor = $visitor;
        $this->newsvisit = $newsvisit;
        $this->arguments = $arguments;
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getNewsvisit(): Newsvisit
    {
        return $this->newsvisit;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
