<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Domain\Repository\LinklistenerRepository;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Signal\SignalTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class LinkClickTracker logs link clicks with data-lux-linklistener="tagname"
 */
class LinkClickTracker
{
    use SignalTrait;

    /**
     * @var Visitor
     */
    protected $visitor = null;

    /**
     * @var LinkclickRepository
     */
    protected $linkclickRepository = null;

    /**
     * @var LinklistenerRepository
     */
    protected $linklistenerRepository = null;

    /**
     * @var PageRepository
     */
    protected $pageRepository = null;

    /**
     * LinkListenerTracker constructor.
     * @param Visitor $visitor
     */
    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
        $this->linkclickRepository = GeneralUtility::makeInstance(LinkclickRepository::class);
        $this->linklistenerRepository = GeneralUtility::makeInstance(LinklistenerRepository::class);
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
    }

    /**
     * @param int $linkclickIdentifier
     * @param int $pageUid
     * @return void
     * @throws Exception
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
            $linkclick->setPage($page)->setVisitor($this->visitor)->setLinklistener($linklistener);
            $this->linkclickRepository->add($linkclick);
            $this->linkclickRepository->persistAll();

            $this->signalDispatch(__CLASS__, 'addLinkClick', [$this->visitor, $linklistener, $pageUid]);
        }
    }
}
