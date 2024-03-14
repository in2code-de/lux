<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Domain\Repository\LinklistenerRepository;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Events\Log\LinkClickEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class LinkClickTracker logs link clicks with data-lux-linklistener="tagname"
 */
class LinkClickTracker
{
    protected ?Visitor $visitor = null;
    protected ?SiteService $siteService = null;
    protected ?LinkclickRepository $linkclickRepository = null;
    protected ?LinklistenerRepository $linklistenerRepository = null;
    protected ?PageRepository $pageRepository = null;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
        $this->siteService = GeneralUtility::makeInstance(SiteService::class);
        $this->linkclickRepository = GeneralUtility::makeInstance(LinkclickRepository::class);
        $this->linklistenerRepository = GeneralUtility::makeInstance(LinklistenerRepository::class);
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
    }

    /**
     * @param int $linkclickIdentifier
     * @param int $pageUid
     * @return void
     * @throws IllegalObjectTypeException
     */
    public function addLinkClick(int $linkclickIdentifier, int $pageUid): void
    {
        /** @var Linklistener $linklistener */
        $linklistener = $this->linklistenerRepository->findByIdentifier($linkclickIdentifier);
        /** @var Page $page */
        $page = $this->pageRepository->findByIdentifier($pageUid);
        if ($linklistener !== null && $page !== null) {
            $linkclick = GeneralUtility::makeInstance(Linkclick::class);
            $linkclick
                ->setPage($page)
                ->setSite($this->siteService->getSiteIdentifierFromPageIdentifier($pageUid))
                ->setVisitor($this->visitor)
                ->setLinklistener($linklistener);
            $this->linkclickRepository->add($linkclick);
            $this->linkclickRepository->persistAll();

            $this->eventDispatcher->dispatch(
                GeneralUtility::makeInstance(LinkClickEvent::class, $this->visitor, $linklistener, $pageUid)
            );
        }
    }
}
