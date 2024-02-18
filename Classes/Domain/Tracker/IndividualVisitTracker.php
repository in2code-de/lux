<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\News;
use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\NewsRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Events\NewsTrackerEvent;
use In2code\Lux\Utility\ObjectUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class IndividualVisitTracker
{
    protected VisitorRepository $visitorRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(VisitorRepository $visitorRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->visitorRepository = $visitorRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Visitor $visitor
     * @param array $arguments
     * @param Pagevisit|null $pagevisit
     * @return void
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws UnknownObjectException
     */
    public function track(Visitor $visitor, array $arguments, Pagevisit $pagevisit = null): void
    {
        if ($this->isTrackingActivated($visitor, $arguments)) {
            die(__CLASS__ . ':' . __LINE__);
        }
    }

    /**
     * @param Visitor $visitor
     * @param array $arguments
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    protected function isTrackingActivated(Visitor $visitor, array $arguments): bool
    {
        return $visitor->isNotBlacklisted() && $this->isTrackingActivatedInSettings();
    }
}
