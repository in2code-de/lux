<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Events\PageTrackerEvent;
use In2code\Lux\Utility\ObjectUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

    public function track(Visitor $visitor, array $arguments): ?Pagevisit
    {
        $pageUid = (int)$arguments['pageUid'];
        $languageUid = (int)$arguments['languageUid'];
        $referrer = $arguments['referrer'];
        $pagevisit = null;
        if ($this->isTrackingActivated($visitor, $pageUid)) {
            $pagevisit = $this->getPagevisit($pageUid, $languageUid, $referrer, $visitor);
            if ($pagevisit !== null) {
                $visitor->addPagevisit($pagevisit);
                $visitor->setVisits($visitor->getNumberOfUniquePagevisits());
                $this->visitorRepository->update($visitor);
                $this->visitorRepository->persistAll();
            }
            $this->eventDispatcher->dispatch(new PageTrackerEvent($visitor, $pagevisit, $arguments));
        }
        return $pagevisit;
    }

    protected function getPagevisit(int $pageUid, int $languageUid, string $referrer, Visitor $visitor): ?Pagevisit
    {
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        $page = $pageRepository->findByUid($pageUid);
        $pagevisit = null;
        if ($page !== null) {
            $pagevisit = GeneralUtility::makeInstance(Pagevisit::class);
            $pagevisit
                ->setPage($page)
                ->setSite($this->siteService->getSiteIdentifierFromPageIdentifier($pageUid))
                ->setLanguage($languageUid)
                ->setReferrer($referrer)
                ->setDomainAutomatically()
                ->setVisitor($visitor);
        }
        return $pagevisit;
    }

    protected function isTrackingActivated(Visitor $visitor, int $pageUid): bool
    {
        return $pageUid > 0 && $visitor->isNotBlacklisted() && $this->isTrackingActivatedInSettings();
    }

    /**
     * Check if tracking of pagevisits is turned on via TypoScript
     *
     * @return bool
     */
    protected function isTrackingActivatedInSettings(): bool
    {
        $configurationService = ObjectUtility::getConfigurationService();
        $settings = $configurationService->getTypoScriptSettings();
        return !empty($settings['tracking']['pagevisits']['_enable'])
            && $settings['tracking']['pagevisits']['_enable'] === '1';
    }
}
