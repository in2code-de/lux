<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Visitor;

final class NewsTrackerEvent
{
    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * @var Newsvisit
     */
    protected $newsvisit;

    /**
     * @param Visitor $visitor
     * @param Newsvisit $newsvisit
     */
    public function __construct(Visitor $visitor, Newsvisit $newsvisit)
    {
        $this->visitor = $visitor;
        $this->newsvisit = $newsvisit;
    }

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * @return Newsvisit
     */
    public function getNewsvisit(): Newsvisit
    {
        return $this->newsvisit;
    }
}
