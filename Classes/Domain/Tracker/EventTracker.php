<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Events\Log\EventTrackerEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

class EventTracker
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function track(Visitor $visitor, array $arguments)
    {
        $this->eventDispatcher->dispatch(new EventTrackerEvent($visitor, $arguments['parameter'], $arguments));
    }
}
