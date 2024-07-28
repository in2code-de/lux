<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Events\Log\VirtualPageTrackerEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

class VirtualPageTracker
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function track(Visitor $visitor, array $arguments)
    {
        $this->eventDispatcher->dispatch(new VirtualPageTrackerEvent($visitor, $arguments['parameter'], $arguments));
    }
}
