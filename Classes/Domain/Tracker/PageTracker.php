<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Events\PageTrackerEvent;
use In2code\Lux\Utility\ObjectUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class PageTracker
{
    protected VisitorRepository $visitorRepository;
    protected SiteService $siteService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        VisitorRepository $visitorRepository,
        SiteService $siteService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->visitorRepository = $visitorRepository;
        $this->siteService = $siteService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Visitor $visitor
     * @param array $arguments
     * @return Pagevisit|null
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws UnknownObjectException
     */
    public function track(Visitor $visitor, array $arguments): ?Pagevisit
    {
        $pageUid = (int)$arguments['pageUid'];
        $languageUid = (int)$arguments['languageUid'];
        $referrer = $arguments['referrer'];
        if ($this->isTrackingActivated($visitor, $pageUid)) {
            $pagevisit = $this->getPageVisit($pageUid, $languageUid, $referrer, $visitor);
            $visitor->addPagevisit($pagevisit);
            $visitor->setVisits($visitor->getNumberOfUniquePagevisits());
            $this->visitorRepository->update($visitor);
            $this->visitorRepository->persistAll();
            $this->eventDispatcher->dispatch(new PageTrackerEvent($visitor, $pagevisit, $arguments));
            return $pagevisit;
        }
        return null;
    }

    /**
     * @param int $pageUid
     * @param int $languageUid
     * @param string $referrer
     * @param Visitor $visitor
     * @return Pagevisit
     */
    protected function getPageVisit(int $pageUid, int $languageUid, string $referrer, Visitor $visitor): Pagevisit
    {
        /** @var Pagevisit $pageVisit */
        $pageVisit = GeneralUtility::makeInstance(Pagevisit::class);
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        /** @var Page $page */
        $page = $pageRepository->findByUid($pageUid);
        $pageVisit
            ->setPage($page)
            ->setSite($this->siteService->getSiteIdentifierFromPageIdentifier($pageUid))
            ->setLanguage($languageUid)
            ->setReferrer($referrer)
            ->setDomainAutomatically()
            ->setVisitor($visitor);
        return $pageVisit;
    }

    /**
     * @param Visitor $visitor
     * @param int $pageUid
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    protected function isTrackingActivated(Visitor $visitor, int $pageUid): bool
    {
        return $pageUid > 0 && $visitor->isNotBlacklisted() && $this->isTrackingActivatedInSettings();
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
