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

/**
 * Class PageTracker
 */
class NewsTracker
{
    /**
     * @var VisitorRepository
     */
    protected $visitorRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param VisitorRepository $visitorRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
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
            $visitor->addNewsvisit($this->getNewsvisit(
                (int)$arguments['newsUid'],
                (int)$arguments['languageUid'],
                $pagevisit
            ));
            $this->visitorRepository->update($visitor);
            $this->visitorRepository->persistAll();
            $this->eventDispatcher->dispatch(GeneralUtility::makeInstance(NewsTrackerEvent::class, $visitor));
        }
    }

    /**
     * @param int $newsUid
     * @param int $languageUid
     * @param Pagevisit|null $pagevisit
     * @return Newsvisit
     */
    protected function getNewsvisit(int $newsUid, int $languageUid, Pagevisit $pagevisit = null): Newsvisit
    {
        $newsvisit = GeneralUtility::makeInstance(Newsvisit::class);
        $newsRepository = GeneralUtility::makeInstance(NewsRepository::class);
        /** @var News $news */
        $news = $newsRepository->findByUid($newsUid);
        $newsvisit->setNews($news)->setLanguage($languageUid)->setDomain()->setPagevisit($pagevisit);
        return $newsvisit;
    }

    /**
     * @param Visitor $visitor
     * @param array $arguments
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    protected function isTrackingActivated(Visitor $visitor, array $arguments): bool
    {
        return !empty($arguments['newsUid']) && $visitor->isNotBlacklisted() && $this->isTrackingActivatedInSettings();
    }

    /**
     * Check if tracking of pagevisits is turned on via TypoScript
     *
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    protected function isTrackingActivatedInSettings(): bool
    {
        $configurationService = ObjectUtility::getConfigurationService();
        $settings = $configurationService->getTypoScriptSettings();
        return !empty($settings['tracking']['pagevisits']['_enable'])
            && $settings['tracking']['pagevisits']['_enable'] === '1';
    }
}
